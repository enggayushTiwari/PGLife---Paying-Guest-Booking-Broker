<?php
session_start();
require "includes/database_connect.php";

$user_id = $_SESSION['user_id'] ?? null;
$property_id = $_GET["property_id"];

// Fetch property details
$sql_1 = "SELECT *, p.id AS property_id, p.name AS property_name, c.name AS city_name 
          FROM properties p
          INNER JOIN cities c ON p.city_id = c.id 
          WHERE p.id = $property_id";
$property = mysqli_fetch_assoc(mysqli_query($conn, $sql_1)) ?: die("Something went wrong!");

// Fetch testimonials
$sql_2 = "SELECT * FROM testimonials WHERE property_id = $property_id";
$testimonials = mysqli_fetch_all(mysqli_query($conn, $sql_2), MYSQLI_ASSOC);

// Fetch amenities
$sql_3 = "SELECT a.* 
          FROM amenities a
          INNER JOIN properties_amenities pa ON a.id = pa.amenity_id
          WHERE pa.property_id = $property_id";
$amenities = mysqli_fetch_all(mysqli_query($conn, $sql_3), MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $property['property_name']; ?> | PG Life</title>
    <?php include "includes/head_links.php"; ?>
    <link href="css/property_detail.css" rel="stylesheet" />
</head>
<body>
    <?php include "includes/header.php"; ?>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb py-2">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="property_list.php?city=<?= $property['city_name']; ?>"><?= $property['city_name']; ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $property['property_name']; ?></li>
        </ol>
    </nav>

    <div id="property-images" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
            <?php foreach (glob("img/properties/" . $property['property_id'] . "/*") as $index => $property_image): ?>
                <li data-target="#property-images" data-slide-to="<?= $index ?>" class="<?= $index == 0 ? 'active' : ''; ?>"></li>
            <?php endforeach; ?>
        </ol>
        <div class="carousel-inner">
            <?php foreach (glob("img/properties/" . $property['property_id'] . "/*") as $index => $property_image): ?>
                <div class="carousel-item <?= $index == 0 ? 'active' : ''; ?>">
                    <img class="d-block w-100" src="<?= $property_image ?>" alt="slide">
                </div>
            <?php endforeach; ?>
        </div>
        <a class="carousel-control-prev" href="#property-images" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#property-images" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>

    <div class="property-summary page-container">
        <div class="row no-gutters justify-content-between">
            <?php
            $total_rating = round(($property['rating_clean'] + $property['rating_food'] + $property['rating_safety']) / 3, 1);
            ?>
            <div class="star-container" title="<?= $total_rating ?>">
                <?php for ($i = 0; $i < 5; $i++): ?>
                    <i class="<?= $total_rating >= $i + 0.8 ? 'fas fa-star' : ($total_rating >= $i + 0.3 ? 'fas fa-star-half-alt' : 'far fa-star') ?>"></i>
                <?php endfor; ?>
            </div>
        </div>
        <div class="detail-container">
            <div class="property-name"><?= $property['property_name'] ?></div>
            <div class="property-address"><?= $property['address'] ?></div>
            <div class="property-gender">
                <img src="img/<?= $property['gender'] == 'male' ? 'male' : ($property['gender'] == 'female' ? 'female' : 'unisex') ?>.png">
            </div>
        </div>
        <div class="row no-gutters">
            <div class="rent-container col-6">
                <div class="rent">₹ <?= number_format($property['rent']) ?>/-</div>
                <div class="rent-unit">per month</div>
            </div>
            <div class="button-container col-6">
    <a href="http://127.0.0.1:5000?amount=<?= $property['rent'] ?>&property_name=<?= urlencode($property['property_name']) ?>&address=<?= urlencode($property['address']) ?>" class="btn btn-primary">Book Now</a>
</div>

        </div>
    </div>

    <div class="property-amenities">
        <div class="page-container">
            <h1>Amenities</h1>
            <?php foreach (['Building', 'Common Area', 'Bedroom'] as $amenity_type): ?>
                <div class="col-md-auto">
                    <h5><?= $amenity_type ?></h5>
                    <?php foreach ($amenities as $amenity): ?>
                        <?php if ($amenity['type'] == $amenity_type): ?>
                            <div class="amenity-container">
                                <img src="img/amenities/<?= $amenity['icon'] ?>.svg">
                                <span><?= $amenity['name'] ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
