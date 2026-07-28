<?php

class Category
{
    private $categoryId;
    private $name;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getCategoryId()
    {
        return $this->categoryId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function addCategory()
    {
        $sql = "INSERT INTO categories (name) 
                VALUES (:name)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ":name" => $this->name
        ]);
    }

    public function getAllCategories()
    {
        $sql = "SELECT * FROM categories 
                ORDER BY category_id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryById($categoryId)
    {
        $sql = "SELECT * FROM categories 
                WHERE category_id = :category_id";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ":category_id" => $categoryId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateCategory()
    {
        $sql = "UPDATE categories 
                SET name = :name 
                WHERE category_id = :category_id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ":name" => $this->name,
            ":category_id" => $this->categoryId
        ]);
    }

    public function deleteCategory($categoryId)
    {
        $sql = "DELETE FROM categories 
                WHERE category_id = :category_id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ":category_id" => $categoryId
        ]);
    }

    public function addProduct()
    {
        echo "Product added to category successfully.<br>";
    }
}

?>