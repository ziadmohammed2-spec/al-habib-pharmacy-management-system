<?php

require_once __DIR__ . "/../database/MySQL.php";

class CartController
{
    private $db;
    private $deliveryFee = 100.00;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function index($customerId)
    {
        $cartId = $this->getOrCreateCart($customerId);

        $sql = "SELECT 
                    ci.cart_item_id,
                    ci.cart_id,
                    ci.product_id,
                    ci.quantity,
                    ci.unit_price,
                    p.name,
                    p.price,
                    p.stock,
                    p.image_url,
                    (ci.quantity * ci.unit_price) AS subtotal
                FROM cart_items ci
                INNER JOIN products p ON ci.product_id = p.product_id
                WHERE ci.cart_id = :cart_id
                ORDER BY ci.cart_item_id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ":cart_id" => $cartId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addToCart($customerId, $productId, $quantity = 1)
    {
        $customerId = (int) $customerId;
        $productId = (int) $productId;
        $quantity = max(1, (int) $quantity);

        if ($customerId <= 0 || $productId <= 0) {
            return false;
        }

        $cartId = $this->getOrCreateCart($customerId);
        $product = $this->getProductById($productId);

        if (!$product || (int) $product["stock"] <= 0) {
            return false;
        }

        $existingItem = $this->getCartItemByProduct($cartId, $productId);

        if ($existingItem) {
            $newQuantity = $existingItem["quantity"] + $quantity;
            if ($newQuantity > (int) $product["stock"]) {
                $newQuantity = (int) $product["stock"];
            }
            return $this->updateQuantity($existingItem["cart_item_id"], $newQuantity);
        }

        if ($quantity > (int) $product["stock"]) {
            $quantity = (int) $product["stock"];
        }

        $sql = "INSERT INTO cart_items (cart_id, product_id, quantity, unit_price)
                VALUES (:cart_id, :product_id, :quantity, :unit_price)";

        $stmt = $this->db->prepare($sql);

        $result = $stmt->execute([
            ":cart_id" => $cartId,
            ":product_id" => $productId,
            ":quantity" => $quantity,
            ":unit_price" => $product["price"]
        ]);

        $this->refreshCartTotal($cartId);
        return $result;
    }

    public function increaseQuantity($cartItemId)
    {
        $item = $this->getCartItemById($cartItemId);

        if (!$item) {
            return false;
        }

        return $this->updateQuantity($cartItemId, $item["quantity"] + 1);
    }

    public function decreaseQuantity($cartItemId)
    {
        $item = $this->getCartItemById($cartItemId);

        if (!$item) {
            return false;
        }

        $newQuantity = $item["quantity"] - 1;

        if ($newQuantity <= 0) {
            return $this->removeItem($cartItemId);
        }

        return $this->updateQuantity($cartItemId, $newQuantity);
    }

    public function updateQuantity($cartItemId, $quantity)
    {
        $cartItemId = (int) $cartItemId;
        $quantity = max(1, (int) $quantity);
        $item = $this->getCartItemById($cartItemId);

        if (!$item) {
            return false;
        }

        $product = $this->getProductById($item["product_id"]);
        if (!$product || (int) $product["stock"] <= 0) {
            return $this->removeItem($cartItemId);
        }

        if ($quantity > (int) $product["stock"]) {
            $quantity = (int) $product["stock"];
        }

        $sql = "UPDATE cart_items
                SET quantity = :quantity
                WHERE cart_item_id = :cart_item_id";

        $stmt = $this->db->prepare($sql);

        $result = $stmt->execute([
            ":quantity" => $quantity,
            ":cart_item_id" => $cartItemId
        ]);

        $this->refreshCartTotal($item["cart_id"]);
        return $result;
    }

    public function removeItem($cartItemId)
    {
        $cartItemId = (int) $cartItemId;
        $item = $this->getCartItemById($cartItemId);

        if (!$item) {
            return false;
        }

        $sql = "DELETE FROM cart_items WHERE cart_item_id = :cart_item_id";
        $stmt = $this->db->prepare($sql);

        $result = $stmt->execute([
            ":cart_item_id" => $cartItemId
        ]);

        $this->refreshCartTotal($item["cart_id"]);
        return $result;
    }

    public function clearCart($customerId)
    {
        $cartId = $this->getOrCreateCart($customerId);

        $sql = "DELETE FROM cart_items WHERE cart_id = :cart_id";
        $stmt = $this->db->prepare($sql);

        $result = $stmt->execute([
            ":cart_id" => $cartId
        ]);

        $this->refreshCartTotal($cartId);
        return $result;
    }

    public function calculateTotal($customerId)
    {
        $cartId = $this->getOrCreateCart($customerId);

        $sql = "SELECT COALESCE(SUM(quantity * unit_price), 0) AS subtotal
                FROM cart_items
                WHERE cart_id = :cart_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ":cart_id" => $cartId
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float) $row["subtotal"];
    }

    public function getCartSummary($customerId)
    {
        $cartId = $this->getOrCreateCart($customerId);
        $subtotal = $this->calculateTotal($customerId);

        $sql = "SELECT COALESCE(SUM(quantity), 0) AS item_count
                FROM cart_items
                WHERE cart_id = :cart_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ":cart_id" => $cartId
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $itemCount = (int) $row["item_count"];
        $deliveryFee = $itemCount > 0 ? $this->deliveryFee : 0;

        return [
            "subtotal" => $subtotal,
            "delivery_fee" => $deliveryFee,
            "total" => $subtotal + $deliveryFee,
            "item_count" => $itemCount
        ];
    }

    public function getRelatedProducts($limit = 5)
    {
        $sql = "SELECT product_id, name, price, stock, image_url
                FROM products
                ORDER BY product_id DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":limit", (int) $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrCreateCart($customerId)
    {
        $customerId = (int) $customerId;
        if ($customerId <= 0) {
            throw new InvalidArgumentException("A valid customer is required for cart actions.");
        }

        $sql = "SELECT cart_id FROM carts WHERE customer_id = :customer_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ":customer_id" => $customerId
        ]);

        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cart) {
            return (int) $cart["cart_id"];
        }

        $sql = "INSERT INTO carts (customer_id, total)
                VALUES (:customer_id, 0)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ":customer_id" => $customerId
        ]);

        return (int) $this->db->lastInsertId();
    }

    private function getProductById($productId)
    {
        $productId = (int) $productId;
        $sql = "SELECT * FROM products WHERE product_id = :product_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ":product_id" => $productId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getCartItemByProduct($cartId, $productId)
    {
        $cartId = (int) $cartId;
        $productId = (int) $productId;
        $sql = "SELECT * FROM cart_items
                WHERE cart_id = :cart_id AND product_id = :product_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ":cart_id" => $cartId,
            ":product_id" => $productId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getCartItemById($cartItemId)
    {
        $cartItemId = (int) $cartItemId;
        $sql = "SELECT * FROM cart_items WHERE cart_item_id = :cart_item_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ":cart_item_id" => $cartItemId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function refreshCartTotal($cartId)
    {
        $sql = "UPDATE carts
                SET total = (
                    SELECT COALESCE(SUM(quantity * unit_price), 0)
                    FROM cart_items
                    WHERE cart_id = :cart_id_for_sum
                )
                WHERE cart_id = :cart_id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ":cart_id_for_sum" => $cartId,
            ":cart_id" => $cartId
        ]);
    }
}

?>
