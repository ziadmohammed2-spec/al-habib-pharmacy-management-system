<?php

require_once __DIR__ . "/../services/OpenFdaService.php";
require_once __DIR__ . "/../services/ProductImageMatcher.php";

class OpenFdaImportController
{
    private $db;
    private $openFdaService;

    public function __construct($db)
    {
        $this->db = $db;
        $this->openFdaService = new OpenFdaService();
    }

    public function importMedicines($limit = 100, $skip = 0)
    {
        $medicines = $this->openFdaService->fetchMedicines($limit, $skip);

        $inserted = 0;

        foreach ($medicines as $medicine) {

            $brandName = $medicine["brand_name"] ?? "Unknown Medicine";
            $genericName = $medicine["generic_name"] ?? null;
            $manufacturerName = $medicine["labeler_name"] ?? null;
            $productNdc = $medicine["product_ndc"] ?? null;
            $dosageForm = $medicine["dosage_form"] ?? null;

            $route = null;

            if (isset($medicine["route"])) {
                if (is_array($medicine["route"])) {
                    $route = implode(", ", $medicine["route"]);
                } else {
                    $route = $medicine["route"];
                }
            }

            if ($this->medicineExists($productNdc, $brandName)) {
                continue;
            }

            $price = rand(30, 250);
            $stock = rand(5, 60);
            $categoryId = 1;
            $companyId = 1;
            $imageUrl = ProductImageMatcher::resolve($brandName, $brandName, $genericName, $dosageForm);
            if ($imageUrl === "") {
                $imageUrl = "assets/images/placeholders/default-product.png";
            }

            $sql = "INSERT INTO products
                    (
                        name,
                        price,
                        stock,
                        category_id,
                        company_id,
                        generic_name,
                        brand_name,
                        manufacturer_name,
                        product_ndc,
                        dosage_form,
                        route,
                        image_url,
                        source
                    )
                    VALUES
                    (
                        :name,
                        :price,
                        :stock,
                        :category_id,
                        :company_id,
                        :generic_name,
                        :brand_name,
                        :manufacturer_name,
                        :product_ndc,
                        :dosage_form,
                        :route,
                        :image_url,
                        :source
                    )";

            $stmt = $this->db->prepare($sql);

            $result = $stmt->execute([
                ":name" => $brandName,
                ":price" => $price,
                ":stock" => $stock,
                ":category_id" => $categoryId,
                ":company_id" => $companyId,
                ":generic_name" => $genericName,
                ":brand_name" => $brandName,
                ":manufacturer_name" => $manufacturerName,
                ":product_ndc" => $productNdc,
                ":dosage_form" => $dosageForm,
                ":route" => $route,
                ":image_url" => $imageUrl,
                ":source" => "openFDA"
            ]);

            if ($result) {
                $inserted++;
            }
        }

        return $inserted;
    }

    private function medicineExists($productNdc, $brandName)
    {
        if (!empty($productNdc)) {
            $sql = "SELECT product_id 
                    FROM products 
                    WHERE product_ndc = :product_ndc 
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                ":product_ndc" => $productNdc
            ]);

            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                return true;
            }
        }

        $sql = "SELECT product_id 
                FROM products 
                WHERE name = :name 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ":name" => $brandName
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }
}

?>
