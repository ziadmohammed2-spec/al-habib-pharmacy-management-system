<?php

class ProductImageMatcher
{
    public static function resolve($name = "", $brandName = "", $genericName = "", $dosageForm = "")
    {
        $haystack = strtolower(trim($name . " " . $brandName . " " . $genericName . " " . $dosageForm));

        $rules = [
            "assets/images/products/equate-daytime-cold-flu.webp" => ["equate daytime", "equate cold", "daytime cold", "dayquil"],
            "assets/images/products/panadol.jpg" => ["panadol", "paracetamol", "acetaminophen", "tylenol"],
            "assets/images/products/vitamin-c-500mg.jpg" => ["vitamin c", "ascorbic", "redoxon"],
            "assets/images/products/amoxicillin-500mg.jpg" => ["amoxicillin", "amoxil"],
            "assets/images/products/ibuprofen-400mg.jpg" => ["ibuprofen", "advil", "brufen", "nurofen"],
            "assets/images/products/aspirin.jpg" => ["aspirin", "acetylsalicylic"],
            "assets/images/products/cetirizine.jpg" => ["cetirizine", "zyrtec", "allergy", "antihistamine"],
            "assets/images/products/azithromycin.jpg" => ["azithromycin", "zithromax"],
            "assets/images/products/omeprazole.jpg" => ["omeprazole", "prilosec", "antacid", "gastric", "heartburn", "reflux", "eno"],
            "assets/images/products/ventolin-inhaler.jpg" => ["ventolin", "albuterol", "salbutamol", "inhaler"],
            "assets/images/products/metformin-500mg.jpg" => ["metformin", "siofor"],
            "assets/images/products/lisinopril-20mg.jpg" => ["lisinopril"],
            "assets/images/products/doxycycline-100mg.jpg" => ["doxycycline"],
            "assets/images/products/celecoxib.avif" => ["celecoxib", "celebrex"],
            "assets/images/products/cineraria-eye-drops.jpg" => ["cineraria", "cineraria maritima", "adel eye"],
        ];

        foreach ($rules as $imageUrl => $keywords) {
            foreach ($keywords as $keyword) {
                if ($keyword !== "" && strpos($haystack, $keyword) !== false) {
                    return $imageUrl;
                }
            }
        }

        return "";
    }
}

?>
