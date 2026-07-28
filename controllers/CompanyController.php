<?php

require_once __DIR__ . "/../models/Company.php";

class CompanyController
{
    private $db;
    private $companyModel;

    public function __construct($db)
    {
        $this->db = $db;
        $this->companyModel = new Company($this->db);
    }

    public function index()
    {
        return $this->companyModel->getAllCompanies();
    }

    public function store($name)
    {
        $name = trim($name);

        if ($name === "") {
            return ["success" => false, "message" => "Company name is required."];
        }

        if ($this->companyNameExists($name)) {
            return ["success" => false, "message" => "This company already exists."];
        }

        $this->companyModel->setName($name);
        $result = $this->companyModel->addCompany();

        return [
            "success" => $result,
            "message" => $result ? "Company added successfully." : "Failed to add company."
        ];
    }

    public function edit($companyId)
    {
        return $this->companyModel->getCompanyById($companyId);
    }

    public function update($companyId, $name)
    {
        $companyId = (int) $companyId;
        $name = trim($name);

        if ($companyId <= 0) {
            return ["success" => false, "message" => "Invalid company ID."];
        }

        if ($name === "") {
            return ["success" => false, "message" => "Company name is required."];
        }

        if ($this->companyNameExists($name, $companyId)) {
            return ["success" => false, "message" => "Another company already has this name."];
        }

        $this->companyModel->setCompanyId($companyId);
        $this->companyModel->setName($name);
        $result = $this->companyModel->updateCompany();

        return [
            "success" => $result,
            "message" => $result ? "Company updated successfully." : "Failed to update company."
        ];
    }

    public function delete($companyId)
    {
        $companyId = (int) $companyId;

        if ($companyId <= 0) {
            return ["success" => false, "message" => "Invalid company ID."];
        }

        $result = $this->companyModel->deleteCompany($companyId);

        return [
            "success" => $result,
            "message" => $result ? "Company deleted successfully." : "Failed to delete company."
        ];
    }

    public function getCompaniesWithProductCount($search = "")
    {
        $search = trim($search);

        $sql = "SELECT c.company_id, c.name, COUNT(p.product_id) AS product_count
                FROM companies c
                LEFT JOIN products p ON p.company_id = c.company_id";

        $params = [];

        if ($search !== "") {
            $sql .= " WHERE c.name LIKE :search";
            $params[":search"] = "%" . $search . "%";
        }

        $sql .= " GROUP BY c.company_id, c.name ORDER BY c.company_id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStats()
    {
        $totalCompanies = (int) $this->db->query("SELECT COUNT(*) FROM companies")->fetchColumn();
        $totalProducts = (int) $this->db->query("SELECT COUNT(*) FROM products")->fetchColumn();

        return [
            "total_companies" => $totalCompanies,
            "active_companies" => $totalCompanies,
            "inactive_companies" => 0,
            "total_products" => $totalProducts
        ];
    }

    private function companyNameExists($name, $ignoreCompanyId = null)
    {
        $sql = "SELECT company_id FROM companies WHERE LOWER(name) = LOWER(:name)";
        $params = [":name" => $name];

        if ($ignoreCompanyId !== null) {
            $sql .= " AND company_id != :company_id";
            $params[":company_id"] = (int) $ignoreCompanyId;
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }
}

?>
