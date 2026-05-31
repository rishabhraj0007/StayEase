<?php
session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

$city   = trim($_GET['city'] ?? '');
$budget = isset($_GET['budget']) && $_GET['budget'] !== '' ? (float)$_GET['budget'] : null;
$gender = trim($_GET['gender'] ?? '');

// Build query dynamically
$sql    = "SELECT * FROM properties WHERE 1=1";
$params = [];

if ($city) {
    $sql .= " AND city = ?";
    $params[] = $city;
}
if ($budget !== null) {
    $sql .= " AND price <= ?";
    $params[] = $budget;
}
if ($gender) {
    $sql .= " AND gender = ?";
    $params[] = $gender;
}
$sql .= " ORDER BY rating DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get interested IDs for logged-in user
$interestedIds = [];
if (isset($_SESSION['user_id'])) {
    $s = $pdo->prepare("SELECT property_id FROM interested_users WHERE user_id = ?");
    $s->execute([$_SESSION['user_id']]);
    $interestedIds = $s->fetchAll(PDO::FETCH_COLUMN);
}

// Build HTML
ob_start();
foreach ($properties as $p):
    $isInterested  = in_array($p['id'], $interestedIds);
    $genderBadge   = 'badge-' . $p['gender'];
    $genderLabel   = $p['gender'] === 'both' ? 'Co-ed' : ucfirst($p['gender']);
?>
<div class="col-md-6 col-lg-4">
    <div class="property-card">
        <div class="card-img-placeholder">
            <i class="bi bi-building"></i>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <div>
                    <p class="property-name"><?= htmlspecialchars($p['name']) ?></p>
                    <p class="property-city"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($p['city']) ?></p>
                </div>
                <span class="rating-badge"><i class="bi bi-star-fill me-1"></i><?= $p['rating'] ?></span>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
                <p class="property-price mb-0">₹<?= number_format($p['price']) ?><span>/month</span></p>
                <span class="badge-gender <?= $genderBadge ?>"><?= $genderLabel ?></span>
            </div>
            <div class="d-flex gap-2">
                <a href="/student-accommodation/property.php?id=<?= $p['id'] ?>" class="btn-view" style="flex:1">View Details</a>
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
<?php endforeach;
$html = ob_get_clean();

echo json_encode([
    'success' => true,
    'html'    => $html,
    'count'   => count($properties)
]);
?>
