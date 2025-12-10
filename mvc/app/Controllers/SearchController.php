<?php
namespace App\Controllers;

use Core\Controller;

class SearchController extends Controller
{
    public function deep()
    {
        // accessible to any authenticated staff (all roles can perform searches across modules?)
        if (empty($_SESSION['role'])) { header('HTTP/1.1 403 Forbidden'); echo 'Access denied'; exit; }

        $q = $_GET['q'] ?? null; // can be MaKH, MaXe, MaHD, MaYC
        if (!$q) { echo 'Missing query parameter q'; exit; }

        $this->loadModel('Search');
        $search = new \App\Models\Search($this->db);
        $results = $search->deep($q);

        $this->db->logAction($_SESSION['user'] ?? 'system', 'deep_search', null, ['q' => $q], $_SERVER['REMOTE_ADDR'] ?? null);

        header('Content-Type: application/json');
        echo json_encode($results, JSON_UNESCAPED_UNICODE);
    }
}
