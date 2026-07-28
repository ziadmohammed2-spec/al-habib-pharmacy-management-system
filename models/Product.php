<?php

class Product
{
    private $db;
    private $productId;
    private $name;
    private $price;
    private $stock;
    private $categoryId;
    private $companyId;
    private $genericName;
    private $brandName;
    private $manufacturerName;
    private $productNdc;
    private $dosageForm;
    private $route;
    private $imageUrl;
    private $source = "local";

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    public function setProductId($productId) { $this->productId = $productId; }
    public function setName($name) { $this->name = $name; }
    public function setPrice($price) { $this->price = $price; }
    public function setStock($stock) { $this->stock = $stock; }
    public function setCategoryId($categoryId) { $this->categoryId = $categoryId; }
    public function setCompanyId($companyId) { $this->companyId = $companyId; }
    public function setGenericName($genericName) { $this->genericName = $genericName; }
    public function setBrandName($brandName) { $this->brandName = $brandName; }
    public function setManufacturerName($manufacturerName) { $this->manufacturerName = $manufacturerName; }
    public function setProductNdc($productNdc) { $this->productNdc = $productNdc; }
    public function setDosageForm($dosageForm) { $this->dosageForm = $dosageForm; }
    public function setRoute($route) { $this->route = $route; }
    public function setImageUrl($imageUrl) { $this->imageUrl = $imageUrl; }
    public function setSource($source) { $this->source = $source ?: "local"; }

    public function getAllProducts($search = "", $categoryId = null, $companyId = null)
    {
        $sql = "SELECT p.*, c.name AS category_name, co.name AS company_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id
                LEFT JOIN companies co ON p.company_id = co.company_id
                WHERE 1=1";
        $params = [];

        if (trim($search) !== "") {
            $sql .= " AND (p.name LIKE :search OR p.generic_name LIKE :search OR p.brand_name LIKE :search OR p.manufacturer_name LIKE :search OR p.product_ndc LIKE :search OR c.name LIKE :search OR co.name LIKE :search)";
            $params[":search"] = "%" . trim($search) . "%";
        }

        if ($categoryId !== null && (int) $categoryId > 0) {
            $sql .= " AND p.category_id = :category_id";
            $params[":category_id"] = (int) $categoryId;
        }

        if ($companyId !== null && (int) $companyId > 0) {
            $sql .= " AND p.company_id = :company_id";
            $params[":company_id"] = (int) $companyId;
        }

        $sql .= " ORDER BY p.product_id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductById($productId)
    {
        $sql = "SELECT p.*, c.name AS category_name, co.name AS company_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id
                LEFT JOIN companies co ON p.company_id = co.company_id
                WHERE p.product_id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addProduct()
    {
        $sql = "INSERT INTO products
                (name, price, stock, category_id, company_id, generic_name, brand_name, manufacturer_name, product_ndc, dosage_form, route, image_url, source)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $this->name,
            $this->price,
            $this->stock,
            $this->categoryId ?: null,
            $this->companyId ?: null,
            $this->genericName,
            $this->brandName,
            $this->manufacturerName,
            $this->productNdc,
            $this->dosageForm,
            $this->route,
            $this->imageUrl,
            $this->source
        ]);
    }

    public function updateProduct()
    {
        $sql = "UPDATE products 
                SET name = ?,
                    price = ?,
                    stock = ?,
                    category_id = ?,
                    company_id = ?,
                    generic_name = ?,
                    brand_name = ?,
                    manufacturer_name = ?,
                    product_ndc = ?,
                    dosage_form = ?,
                    route = ?,
                    image_url = ?,
                    source = ?
                WHERE product_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $this->name,
            $this->price,
            $this->stock,
            $this->categoryId ?: null,
            $this->companyId ?: null,
            $this->genericName,
            $this->brandName,
            $this->manufacturerName,
            $this->productNdc,
            $this->dosageForm,
            $this->route,
            $this->imageUrl,
            $this->source,
            $this->productId
        ]);
    }

    public function deleteProduct($productId)
    {
        $sql = "DELETE FROM products WHERE product_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$productId]);
    }
}

?>
