<?php
// htdocs/index.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/app_core/config/database.php';
require_once __DIR__ . '/app_core/modules/catalog/controllers/CatalogController.php';

$activeModule = $_GET['module'] ?? 'catalog';
$catalogController = new CatalogController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $catalogController->store($_POST, $_FILES['image'] ?? null);
    header("Location: ?module=catalog");
    exit;
}

$products = $catalogController->index();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maweli Trading Dashboard</title>

    <!-- Bootstrap 5 CSS & Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --bg-body: #f4f5f7;
            --sidebar-bg: #121212;
            --accent-red: #dc3545;
            --accent-red-hover: #bb2d3b;
            --card-radius: 16px;
        }

        body {
            background-color: var(--bg-body);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1a1a1a;
        }

        /* PC Layout Sidebar */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            padding: 24px 16px;
        }

        .sidebar .brand-title {
            color: #ffffff;
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 0 12px 24px 12px;
            border-bottom: 1px solid #2a2a2a;
        }

        .sidebar .nav-link {
            color: #a0a0a0;
            font-weight: 500;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 6px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar .nav-link:hover {
            color: #ffffff;
            background-color: #222222;
        }

        .sidebar .nav-link.active {
            color: #ffffff;
            background-color: var(--accent-red);
        }

        .main-wrapper {
            margin-left: 260px;
            padding: 32px;
            min-height: 100vh;
        }

        /* Modern Content Cards */
        .dashboard-card {
            background: #ffffff;
            border-radius: var(--card-radius);
            border: 1px solid #e9ecef;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            margin-bottom: 24px;
        }

        /* Red Primary Buttons */
        .btn-danger {
            background-color: var(--accent-red);
            border-color: var(--accent-red);
            border-radius: 10px;
            font-weight: 600;
            padding: 10px 20px;
        }

        .btn-danger:hover {
            background-color: var(--accent-red-hover);
            border-color: var(--accent-red-hover);
        }

        /* Form Inputs Styling */
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 10px 14px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-red);
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.15);
        }

        /* Tables Styling */
        .table {
            vertical-align: middle;
        }

        .table thead th {
            background-color: #f8f9fa;
            color: #6c757d;
            font-size: 0.825rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #edf2f7;
            padding: 12px 16px;
        }

        /* Mobile Bottom Nav Dock */
        .mobile-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #121212;
            padding: 10px 16px;
            z-index: 1050;
            border-top: 1px solid #222;
        }

        .mobile-nav .nav-item {
            flex: 1;
            text-align: center;
        }

        .mobile-nav .nav-link {
            color: #888888;
            font-size: 0.75rem;
            padding: 6px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .mobile-nav .nav-link.active {
            color: var(--accent-red);
        }

        .mobile-nav .nav-link i {
            font-size: 1.25rem;
        }

        /* Responsive Overrides */
        @media (max-width: 991.98px) {
            .sidebar { display: none; }
            .main-wrapper { margin-left: 0; padding: 16px; padding-bottom: 80px; }
            .mobile-nav { display: flex; }
        }

        /* Print Override */
        @media print {
            .sidebar, .mobile-nav, .no-print { display: none !important; }
            .main-wrapper { margin-left: 0 !important; padding: 0 !important; }
            body { background: #ffffff !important; }
            #printableArea { border: none !important; box-shadow: none !important; padding: 0 !important; }
            @page { size: A4 portrait; margin: 15mm; }
        }
    </style>
</head>
<body>

    <!-- PC SIDEBAR NAV -->
    <div class="sidebar">
        <div class="brand-title d-flex align-items-center gap-2">
            <i class="bi bi-box-seam-fill text-danger fs-4"></i>
            <span>Maweli Trading</span>
        </div>
        <nav class="nav flex-column mt-3">
            <a href="?module=catalog" class="nav-link <?= $activeModule === 'catalog' ? 'active' : '' ?>">
                <i class="bi bi-grid-3x3-gap"></i> Product Catalogue
            </a>
            <a href="?module=inventory" class="nav-link <?= $activeModule === 'inventory' ? 'active' : '' ?>">
                <i class="bi bi-boxes"></i> Inventory Logs
            </a>
            <a href="?module=invoice" class="nav-link <?= $activeModule === 'invoice' ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text"></i> Invoice Generator
            </a>
            <a href="?module=notes" class="nav-link <?= $activeModule === 'notes' ? 'active' : '' ?>">
                <i class="bi bi-journal-text"></i> Daily Notes
            </a>
            <a href="?module=dispatch" class="nav-link <?= $activeModule === 'dispatch' ? 'active' : '' ?>">
                <i class="bi bi-truck"></i> Dispatch Tracking
            </a>
        </nav>
    </div>

    <!-- MAIN VIEW WRAPPER -->
    <div class="main-wrapper">
        <div class="dashboard-card">
            <?php 
            if ($activeModule === 'inventory') {
                $viewPath = __DIR__ . '/app_core/modules/inventory/views/index.php';
            } elseif ($activeModule === 'invoice') {
                $viewPath = __DIR__ . '/app_core/modules/invoice/views/index.php';
            } elseif ($activeModule === 'notes') {
                $viewPath = __DIR__ . '/app_core/modules/notes/views/index.php';
            } elseif ($activeModule === 'dispatch') {
                $viewPath = __DIR__ . '/app_core/modules/dispatch/views/index.php';
            } else {
                $viewPath = __DIR__ . '/app_core/modules/catalog/views/index.php';
            }

            if (file_exists($viewPath)) {
                include $viewPath;
            } else {
                echo "<div class='alert alert-danger'>Module view file not found.</div>";
            }
            ?>
        </div>
    </div>

    <!-- MOBILE BOTTOM NAVIGATION DOCK -->
    <div class="mobile-nav justify-content-around">
        <div class="nav-item">
            <a href="?module=catalog" class="nav-link <?= $activeModule === 'catalog' ? 'active' : '' ?>">
                <i class="bi bi-grid-3x3-gap"></i>
                <span>Catalogue</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="?module=inventory" class="nav-link <?= $activeModule === 'inventory' ? 'active' : '' ?>">
                <i class="bi bi-boxes"></i>
                <span>Stock</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="?module=invoice" class="nav-link <?= $activeModule === 'invoice' ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text"></i>
                <span>Invoice</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="?module=notes" class="nav-link <?= $activeModule === 'notes' ? 'active' : '' ?>">
                <i class="bi bi-journal-text"></i>
                <span>Notes</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="?module=dispatch" class="nav-link <?= $activeModule === 'dispatch' ? 'active' : '' ?>">
                <i class="bi bi-truck"></i>
                <span>Dispatch</span>
            </a>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>