<?php
require_once 'config/db.php';
require_once 'includes/header.php';

// Fetch distinct cities for filter dropdown
$cities = $pdo->query("SELECT DISTINCT city FROM properties ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);

// Fetch all properties (initial load — AJAX will handle filtering)
$stmt = $pdo->query("SELECT * FROM properties ORDER BY rating DESC");
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get interested property IDs for logged-in user
$interestedIds = [];
if (isset($_SESSION['user_id'])) {
    $s = $pdo->prepare("SELECT property_id FROM interested_users WHERE user_id = ?");
    $s->execute([$_SESSION['user_id']]);
    $interestedIds = $s->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!-- HERO -->
<section class="hero">
    <div class="container text-center">
        <h1><i class="bi bi-geo-alt-fill me-2 text-warning"></i>Find Your Perfect PG</h1>
        <p class="mt-2">Explore hundreds of PG accommodations across India — safe, affordable & verified</p>
        <div class="d-flex justify-content-center gap-3 mt-3">
            <span class="badge bg-white text-dark px-3 py-2"><i class="bi bi-shield-check text-success me-1"></i>Verified Listings</span>
            <span class="badge bg-white text-dark px-3 py-2"><i class="bi bi-star-fill text-warning me-1"></i>Rated PGs</span>
            <span class="badge bg-white text-dark px-3 py-2"><i class="bi bi-people-fill text-primary me-1"></i>Student Friendly</span>
        </div>
    </div>
</section>

<!-- FILTER BAR -->
<div class="container">
    <div class="filter-bar">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">City</label>
                <select id="filter-city">
                    <option value="">All Cities</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?= htmlspecialchars($city) ?>"><?= htmlspecialchars($city) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Max Budget (₹/month)</label>
                <input type="number" id="filter-budget" placeholder="e.g. 8000" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Gender</label>
                <select id="filter-gender">
                    <option value="">All</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="both">Co-ed (Both)</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn-filter" onclick="applyFilters()">
                    <i class="bi bi-funnel-fill me-2"></i>Search
                </button>
            </div>
        </div>
    </div>
</div>

<!-- RESULTS SECTION -->
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0" id="results-count">
            Showing <span class="text-primary"><?= count($properties) ?></span> properties
        </h5>
        <span class="text-muted small">Sorted by rating</span>
    </div>

    <!-- Loading Spinner -->
    <div id="loading-spinner">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2">Searching properties...</p>
    </div>

    <!-- Property Grid -->
    <div class="row g-4" id="properties-grid">
        <?php foreach ($properties as $p): ?>
            <?php
            $isInterested = in_array($p['id'], $interestedIds);
            $genderBadge = 'badge-' . $p['gender'];
            $genderLabel = $p['gender'] === 'both' ? 'Co-ed' : ucfirst($p['gender']);
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="property-card">
                    <!-- Image -->
                    <?php if (!empty($p['image']) && file_exists('images/' . $p['image'])): ?>
                        <img src="/student-accommodation/images/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                    <?php else: ?>
                        <div class="card-img-placeholder">
                            <i class="bi bi-building"></i>
                        </div>
                    <?php endif; ?>

                    <div class="card-body">
                        <!-- Name & City -->
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <p class="property-name"><?= htmlspecialchars($p['name']) ?></p>
                                <p class="property-city"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($p['city']) ?></p>
                            </div>
                            <span class="rating-badge"><i class="bi bi-star-fill me-1"></i><?= $p['rating'] ?></span>
                        </div>

                        <!-- Price & Gender -->
                        <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
                            <p class="property-price mb-0">₹<?= number_format($p['price']) ?><span>/month</span></p>
                            <span class="badge-gender <?= $genderBadge ?>"><?= $genderLabel ?></span>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex gap-2">
                            <a href="/student-accommodation/property.php?id=<?= $p['id'] ?>" class="btn-view" style="flex:1">
                                View Details
                            </a>
                            <button
                                class="btn-interest <?= $isInterested ? 'interested' : '' ?>"
                                style="flex:0 0 44px; padding: 8px;"
                                onclick="toggleInterest(this, <?= $p['id'] ?>)"
                                title="<?= $isInterested ? 'Remove Interest' : 'Mark as Interested' ?>"
                            >
                                <i class="bi <?= $isInterested ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- No Results -->
    <div id="no-results" class="no-results" style="display:none;">
        <i class="bi bi-search"></i>
        <h5>No properties found</h5>
        <p class="small">Try adjusting your filters</p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
