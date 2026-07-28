<?php

require_once __DIR__ . "/../models/Category.php";

class CategoryController
{
    private $db;
    private $categoryModel;

    public function __construct($db)
    {
        $this->db = $db;
        $this->categoryModel = new Category($this->db);
    }

    public function index()
    {
        return $this->categoryModel->getAllCategories();
    }

    public function store($name)
    {
        $name = trim((string) $name);
        if ($name === "") {
            return false;
        }

        $this->categoryModel->setName($name);

        return $this->categoryModel->addCategory();
    }

    public function edit($categoryId)
    {
        return $this->categoryModel->getCategoryById($categoryId);
    }

    public function update($categoryId, $name)
    {
        $categoryId = (int) $categoryId;
        $name = trim((string) $name);

        if ($categoryId <= 0 || $name === "") {
            return false;
        }

        $this->categoryModel->setCategoryId($categoryId);
        $this->categoryModel->setName($name);

        return $this->categoryModel->updateCategory();
    }

    public function delete($categoryId)
    {
        $categoryId = (int) $categoryId;
        if ($categoryId <= 0) {
            return false;
        }

        return $this->categoryModel->deleteCategory($categoryId);
    }
}

?>
