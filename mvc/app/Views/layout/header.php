<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Vehicle Insurance Management System'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
    
    <?php if (isset($customCSS)): ?>
        <?php foreach ((array)$customCSS as $css): ?>
            <link href="<?php echo BASE_URL; ?><?php echo $css; ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navigation Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <!-- Brand -->
            <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>">
                <i class="fas fa-shield-alt"></i> Insurance Management
            </a>
            
            <!-- Navbar Toggler -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($auth && $auth->isLoggedIn()): ?>
                        <!-- Dashboard Link -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>?c=Home&m=index">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        
                        <!-- Module Links - Shown based on role -->
                        <?php if (in_array($auth->getRole(), ['Customer Staff', 'Vehicle Staff', 'Claims Staff', 'Accounting Staff'])): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-cog"></i> Modules
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>?c=Customer&m=list"><i class="fas fa-users"></i> Customers</a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>?c=Vehicle&m=list"><i class="fas fa-car"></i> Vehicles</a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>?c=Claims&m=list"><i class="fas fa-file-alt"></i> Claims</a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>?c=Accounting&m=list"><i class="fas fa-calculator"></i> Accounting</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>?c=Search&m=query"><i class="fas fa-search"></i> Search</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                        
                        <!-- User Info & Logout -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle"></i> 
                                <?php echo htmlspecialchars($auth->getUsername()); ?>
                                <small class="badge bg-primary"><?php echo htmlspecialchars($auth->getRole()); ?></small>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>?c=User&m=profile"><i class="fas fa-id-card"></i> My Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>?c=Auth&m=logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Login Link -->
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light btn-sm ms-2" href="<?php echo BASE_URL; ?>?c=Auth&m=login">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['warning'])): ?>
        <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['warning']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['warning']); ?>
    <?php endif; ?>

    <!-- Main Content Container -->
    <main class="container-fluid py-4">
