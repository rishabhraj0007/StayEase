<?php
require_once 'config/db.php';
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: index.php'); exit; }

// Fetch property
$stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->execute([$id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$property) { header('Location: index.php'); exit; }

// Fetch amenities
$amenStmt = $pdo->prepare("
    SELECT a.name FROM amenities a
    JOIN property_amenities pa ON a.id = pa.amenity_id
    WHERE pa.property_id = ?
");
$amenStmt->execute([$id]);
$amenities = $amenStmt->fetchAll(PDO::FETCH_COLUMN);

// Check if current user is interested
$isInterested = false;
if (isset($_SESSION['user_id'])) {
    $s = $pdo->prepare("SELECT 1 FROM interested_users WHERE user_id = ? AND property_id = ?");
    $s->execute([$_SESSION['user_id'], $id]);
    $isInterested = (bool)$s->fetch();
}

$genderMap = ['male' => 'Male Only', 'female' => 'Female Only', 'both' => 'Co-ed (Both)'];
$genderBadge = 'badge-' . $property['gender'];

// Amenity icons mapping
$amenityIcons = [
    'WiFi' => 'bi-wifi', 'AC' => 'bi-thermometer-snow', 'Laundry' => 'bi-basket',
    'Meals Included' => 'bi-cup-hot-fill', 'Parking' => 'bi-p-circle',
    'Hot Water' => 'bi-droplet-fill', 'TV' => 'bi-tv', 'Study Room' => 'bi-book',
    'Security' => 'bi-shield-check', 'Power Backup' => 'bi-lightning-charge'
];
?>

<div class="container my-5">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/student-accommodation/index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($property['name']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Left: Image + Details -->
        <div class="col-lg-8">
            <div class="detail-hero">
                <!-- Image -->
                <?php if (!empty($property['image']) && file_exists('images/' . $property['image'])): ?>
                    <img src="/student-accommodation/images/<?= htmlspecialchars($property['image']) ?>" alt="<?= htmlspecialchars($property['name']) ?>" class="mb-4">
                <?php else: ?>
                    <div class="card-img-placeholder rounded mb-4" style="height:300px; border-radius:10px !important">
                        <i class="bi bi-building"></i>
                    </div>
                <?php endif; ?>

                <!-- Name & Location -->
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <div>
                        <h2 class="fw-bold mb-1"><?= htmlspecialchars($property['name']) ?></h2>
                        <p class="text-muted mb-0">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                            <?= htmlspecialchars($property['address'] ?? $property['city']) ?>
                        </p>
                    </div>
                    <span class="rating-badge fs-6 px-3 py-2">
                        <i class="bi bi-star-fill me-1"></i><?= $property['rating'] ?> / 5.0
                    </span>
                </div>

                <hr>

                <!-- Description -->
                <h5 class="fw-semibold mb-2">About this PG</h5>
                <p class="text-muted"><?= htmlspecialchars($property['description'] ?? 'No description available.') ?></p>

                <hr>

                <!-- Amenities -->
                <h5 class="fw-semibold mb-3">Amenities</h5>
                <?php if ($amenities): ?>
                    <div>
                        <?php foreach ($amenities as $amenity): ?>
                            <?php $icon = $amenityIcons[$amenity] ?? 'bi-check-circle'; ?>
                            <span class="amenity-chip">
                                <i class="bi <?= $icon ?> text-primary"></i>
                                <?= htmlspecialchars($amenity) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No amenities listed.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Booking Card -->
        <div class="col-lg-4">
            <div class="detail-hero" style="position: sticky; top: 80px;">
                <p class="text-muted small text-uppercase fw-semibold mb-1">Monthly Rent</p>
                <h2 class="fw-bold text-primary mb-1">₹<?= number_format($property['price']) ?></h2>
                <p class="text-muted small mb-3">per month</p>

                <hr>

                <table class="table table-borderless table-sm mb-3">
                    <tr>
                        <td class="text-muted small">City</td>
                        <td class="fw-semibold small"><?= htmlspecialchars($property['city']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Gender</td>
                        <td>
                            <span class="badge-gender <?= $genderBadge ?> small">
                                <?= $genderMap[$property['gender']] ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Rating</td>
                        <td class="fw-semibold small">
                            <i class="bi bi-star-fill text-warning me-1"></i><?= $property['rating'] ?>
                        </td>
                    </tr>
                </table>

                <!-- Interest Button -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button
                        id="interest-btn"
                        class="btn w-100 mb-2 <?= $isInterested ? 'btn-danger' : 'btn-outline-danger' ?>"
                        onclick="toggleInterest(this, <?= $property['id'] ?>)"
                        style="border-radius: 8px; font-weight: 600;"
                    >
                        <i class="bi <?= $isInterested ? 'bi-heart-fill' : 'bi-heart' ?> me-2"></i>
                        <?= $isInterested ? 'Remove Interest' : 'Mark as Interested' ?>
                    </button>
                <?php else: ?>
                    <a href="/student-accommodation/login.php" class="btn btn-outline-danger w-100 mb-2" style="border-radius:8px; font-weight:600;">
                        <i class="bi bi-heart me-2"></i>Login to Show Interest
                    </a>
                <?php endif; ?>

                <a href="/student-accommodation/index.php" class="btn btn-light w-100" style="border-radius:8px;">
                    <i class="bi bi-arrow-left me-2"></i>Back to Listings
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
