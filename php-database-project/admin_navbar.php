<?php
// admin_navbar.php
?>
<!-- top brand row -->
<div style="background: #f8f9fa; border-bottom: 1px solid #ddd; padding: 0.5rem 0;">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div>
            <span style="font-weight:600; font-size:1.1rem; vertical-align:middle;">
                <!-- <img src="logo.png" alt="bio-well insurance company" style="height:28px; vertical-align:middle; margin-right:8px;"> -->
                Bio-Well Insurance Company
            </span>
        </div>
        <div>
            <a href="index.php?page=logout" class="btn btn-outline-secondary">Log Out</a>
        </div>
    </div>
</div>
<!-- icon navbar row -->
<div style="background: #ededed; border-bottom: 2px solid #ccc;">
    <div class="container-fluid">
        <ul class="nav justify-content-center" style="gap:2.5rem; padding:0.5rem 0;">
            <li class="nav-item">
                <a class="nav-link" href="admin_home.php" style="color:#222;">
                    <span style="display:block; text-align:center; font-size:1.3rem;"><i class="bi bi-house"></i></span>
                    <span style="font-size:0.95rem;">Home</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_tickets.php" style="color:#222;">
                    <span style="display:block; text-align:center; font-size:1.3rem;"><i class="bi bi-ticket"></i></span>
                    <span style="font-size:0.95rem;">Tickets</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_quotations.php" style="color:#222;">
                    <span style="display:block; text-align:center; font-size:1.3rem;"><i class="bi bi-chat-square-text"></i></span>
                    <span style="font-size:0.95rem;">Quotations</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_agents.php" style="color:#222;">
                    <span style="display:block; text-align:center; font-size:1.3rem;"><i class="bi bi-person"></i></span>
                    <span style="font-size:0.95rem;">Agents</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_products.php" style="color:#222;">
                    <span style="display:block; text-align:center; font-size:1.3rem;"><i class="bi bi-box"></i></span>
                    <span style="font-size:0.95rem;">Products</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?page=profile" style="color:#222;">
                    <span style="display:block; text-align:center; font-size:1.3rem;"><i class="bi bi-person-circle"></i></span>
                    <span style="font-size:0.95rem;">Profile</span>
                </a>
            </li>
        </ul>
    </div>
</div>
