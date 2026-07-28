<?php

class Company
{
    private $companyId;
    private $name;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getCompanyId()
    {
        return $this->companyId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function addCompany()
    {
        $sql = "INSERT INTO companies (name) VALUES (:name)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ":name" => $this->name
        ]);
    }

    public function getAllCompanies()
    {
        $sql = "SELECT * FROM companies ORDER BY company_id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCompanyById($companyId)
    {
        $sql = "SELECT * FROM companies WHERE company_id = :company_id";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ":company_id" => $companyId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateCompany()
    {
        $sql = "UPDATE companies 
                SET name = :name 
                WHERE company_id = :company_id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ":name" => $this->name,
            ":company_id" => $this->companyId
        ]);
    }

    public function deleteCompany($companyId)
    {
        $sql = "DELETE FROM companies WHERE company_id = :company_id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ":company_id" => $companyId
        ]);
    }

    public function addProduct()
    {
        echo "This company can have products added to it.<br>";
    }
}

?>