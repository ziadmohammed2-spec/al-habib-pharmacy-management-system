<?php

require_once __DIR__ . "/../models/Product.php";
require_once __DIR__ . "/../services/ProductImageMatcher.php";

class ProductController
{
    private $productModel;

    public function __construct($db)
    {
        $this->productModel = new Product($db);
    }

    public function index($search = "", $categoryId = null, $companyId = null)
    {
        return $this->productModel->getAllProducts($search, $categoryId, $companyId);
    }

    public function show($productId)
    {
        $productId = (int) $productId;
        if ($productId <= 0) {
            return null;
        }

        return $this->productModel->getProductById($productId);
    }

    public function store($name, $price, $stock, $categoryId, $companyId, $extra = [])
    {
        $validation = $this->applyProductData($name, $price, $stock, $categoryId, $companyId, $extra);
        if (!$validation["success"]) {
            return $validation;
        }

        $result = $this->productModel->addProduct();
        return [
            "success" => $result,
            "message" => $result ? "Product added successfully." : "Failed to add product."
        ];
    }

    public function update($productId, $name, $price, $stock, $categoryId, $companyId, $extra = [])
    {
        $productId = (int) $productId;
        if ($productId <= 0) {
            return ["success" => false, "message" => "Invalid product ID."];
        }

        $validation = $this->applyProductData($name, $price, $stock, $categoryId, $companyId, $extra);
        if (!$validation["success"]) {
            return $validation;
        }

        $this->productModel->setProductId($productId);

        $result = $this->productModel->updateProduct();
        return [
            "success" => $result,
            "message" => $result ? "Product updated successfully." : "Failed to update product."
        ];
    }

    public function delete($productId)
    {
        $productId = (int) $productId;
        if ($productId <= 0) {
            return false;
        }

        return $this->productModel->deleteProduct($productId);
    }

    private function applyProductData($name, $price, $stock, $categoryId, $companyId, $extra = [])
    {
        $name = trim((string) $name);
        $price = (float) $price;
        $stock = (int) $stock;

        if ($name === "") {
            return ["success" => false, "message" => "Product name is required."];
        }

        if ($price < 0) {
            return ["success" => false, "message" => "Price cannot be negative."];
        }

        if ($stock < 0) {
            return ["success" => false, "message" => "Stock cannot be negative."];
        }

        $this->productModel->setName($name);
        $this->productModel->setPrice($price);
        $this->productModel->setStock($stock);
        $this->productModel->setCategoryId((int) $categoryId);
        $this->productModel->setCompanyId((int) $companyId);
        $this->productModel->setGenericName(trim($extra["generic_name"] ?? ""));
        $this->productModel->setBrandName(trim($extra["brand_name"] ?? ""));
        $this->productModel->setManufacturerName(trim($extra["manufacturer_name"] ?? ""));
        $this->productModel->setProductNdc(trim($extra["product_ndc"] ?? ""));
        $this->productModel->setDosageForm(trim($extra["dosage_form"] ?? ""));
        $this->productModel->setRoute(trim($extra["route"] ?? ""));
        $imageUrl = trim($extra["image_url"] ?? "");
        if ($imageUrl === "") {
            $imageUrl = ProductImageMatcher::resolve(
                $name,
                $extra["brand_name"] ?? "",
                $extra["generic_name"] ?? "",
                $extra["dosage_form"] ?? ""
            );
        }
        if ($imageUrl === "") {
            $imageUrl = "assets/images/placeholders/default-product.png";
        }
        $this->productModel->setImageUrl($imageUrl);
        $this->productModel->setSource(trim($extra["source"] ?? "local"));

        return ["success" => true, "message" => ""];
    }
}

?>
