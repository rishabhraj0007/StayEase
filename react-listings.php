<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$stmt = $pdo->query("SELECT * FROM properties ORDER BY rating DESC");
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

$interestedIds = [];
if (isset($_SESSION['user_id'])) {
    $s = $pdo->prepare("SELECT property_id FROM interested_users WHERE user_id = ?");
    $s->execute([$_SESSION['user_id']]);
    $interestedIds = $s->fetchAll(PDO::FETCH_COLUMN);
}

$propertiesJson = json_encode($properties);
$interestedJson = json_encode(array_map('intval', $interestedIds));
$loggedIn       = isset($_SESSION['user_id']) ? 'true' : 'false';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayEase – React Listings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/student-accommodation/css/style.css" rel="stylesheet">
    <style>
        .shortlist-panel {
            position: fixed; top: 80px; right: 20px; width: 280px;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10); z-index: 100;
            max-height: 80vh; overflow-y: auto;
        }
        .shortlist-item { font-size: 0.82rem; padding: 8px 12px; border-bottom: 1px solid #f1f5f9; }
        .shortlist-item:last-child { border-bottom: none; }
        .react-badge {
            display: inline-block; background: #dbeafe; color: #1d4ed8;
            font-size: 0.72rem; font-weight: 700; padding: 2px 10px;
            border-radius: 20px; margin-left: 8px; vertical-align: middle;
        }
        .tab-btn {
            border: none; background: none; font-size: 0.9rem; font-weight: 600;
            color: #94a3b8; padding: 8px 16px; border-bottom: 2px solid transparent;
            cursor: pointer; transition: all 0.2s;
        }
        .tab-btn.active { color: #2563eb; border-bottom-color: #2563eb; }
    </style>
</head>
<body>

<?php require_once 'includes/header.php'; ?>

<script>
    const ALL_PROPERTIES = <?= $propertiesJson ?>;
    const INTERESTED_IDS = <?= $interestedJson ?>;
    const IS_LOGGED_IN   = <?= $loggedIn ?>;
</script>

<div id="react-root"></div>

<script src="https://cdn.jsdelivr.net/npm/react@18/umd/react.development.js"></script>
<script src="https://cdn.jsdelivr.net/npm/react-dom@18/umd/react-dom.development.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@babel/standalone/babel.min.js"></script>

<script type="text/babel">
const { useState } = React;

function GenderBadge({ gender }) {
    const map = { male: ['badge-male','Male'], female: ['badge-female','Female'], both: ['badge-both','Co-ed'] };
    const [cls, label] = map[gender] || ['badge-both', gender];
    return <span className={`badge-gender ${cls}`}>{label}</span>;
}

function PropertyCard({ property, isShortlisted, onToggleShortlist }) {
    const [interested, setInterested] = useState(INTERESTED_IDS.includes(property.id));
    const [loading, setLoading] = useState(false);

    function handleInterest() {
        if (!IS_LOGGED_IN) { window.location.href = '/student-accommodation/login.php'; return; }
        setLoading(true);
        fetch('/student-accommodation/ajax/toggle_interest.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'property_id=' + property.id
        })
        .then(r => r.json())
        .then(data => { if (data.success) setInterested(data.action === 'added'); setLoading(false); })
        .catch(() => setLoading(false));
    }

    return (
        <div className="col-md-6 col-lg-4">
            <div className="property-card">
                <div className="card-img-placeholder">
                    <i className="bi bi-building"></i>
                </div>
                <div className="card-body">
                    <div className="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <p className="property-name">{property.name}</p>
                            <p className="property-city"><i className="bi bi-geo-alt me-1"></i>{property.city}</p>
                        </div>
                        <span className="rating-badge"><i className="bi bi-star-fill me-1"></i>{property.rating}</span>
                    </div>
                    <div className="d-flex justify-content-between align-items-center mt-2 mb-3">
                        <p className="property-price mb-0">
                            ₹{parseInt(property.price).toLocaleString('en-IN')}<span>/month</span>
                        </p>
                        <GenderBadge gender={property.gender} />
                    </div>
                    <div className="d-flex gap-2 mb-2">
                        <a href={`/student-accommodation/property.php?id=${property.id}`} className="btn-view" style={{flex:1}}>
                            View Details
                        </a>
                        <button
                            className={`btn-interest ${interested ? 'interested' : ''}`}
                            style={{flex:'0 0 44px', padding:'8px'}}
                            onClick={handleInterest}
                            disabled={loading}
                        >
                            <i className={`bi ${interested ? 'bi-heart-fill' : 'bi-heart'}`}></i>
                        </button>
                    </div>
                    <button
                        onClick={() => onToggleShortlist(property)}
                        className="btn btn-sm w-100"
                        style={{
                            borderRadius:'8px', fontSize:'0.8rem', fontWeight:600,
                            border: isShortlisted ? '1.5px solid #f59e0b' : '1.5px solid #e2e8f0',
                            background: isShortlisted ? '#fef3c7' : '#f8fafc',
                            color: isShortlisted ? '#92400e' : '#64748b'
                        }}
                    >
                        <i className={`bi ${isShortlisted ? 'bi-bookmark-fill' : 'bi-bookmark'} me-1`}></i>
                        {isShortlisted ? 'Shortlisted' : 'Add to Shortlist'}
                    </button>
                </div>
            </div>
        </div>
    );
}

function ShortlistPanel({ shortlist, onRemove }) {
    if (shortlist.length === 0) return (
        <div className="shortlist-panel p-3 text-center">
            <i className="bi bi-bookmark text-muted" style={{fontSize:'1.8rem'}}></i>
            <p className="text-muted small mt-2 mb-0">Your shortlist is empty.</p>
        </div>
    );
    return (
        <div className="shortlist-panel">
            <div className="p-3 border-bottom">
                <h6 className="mb-0"><i className="bi bi-bookmark-fill text-warning me-2"></i>Shortlist ({shortlist.length})</h6>
            </div>
            {shortlist.map(p => (
                <div key={p.id} className="shortlist-item d-flex justify-content-between align-items-center">
                    <div>
                        <p className="mb-0 fw-semibold" style={{fontSize:'0.82rem'}}>{p.name}</p>
                        <p className="mb-0 text-muted" style={{fontSize:'0.75rem'}}>{p.city} · ₹{parseInt(p.price).toLocaleString('en-IN')}</p>
                    </div>
                    <button onClick={() => onRemove(p.id)} style={{background:'none',border:'none'}} className="text-danger ms-2">
                        <i className="bi bi-x-circle"></i>
                    </button>
                </div>
            ))}
        </div>
    );
}

function App() {
    const [properties, setProperties]     = useState(ALL_PROPERTIES);
    const [shortlist, setShortlist]       = useState([]);
    const [activeTab, setActiveTab]       = useState('all');
    const [filterCity, setFilterCity]     = useState('');
    const [filterBudget, setFilterBudget] = useState('');
    const [filterGender, setFilterGender] = useState('');

    const cities = [...new Set(ALL_PROPERTIES.map(p => p.city))].sort();

    function applyFilters() {
        let filtered = ALL_PROPERTIES;
        if (filterCity)   filtered = filtered.filter(p => p.city === filterCity);
        if (filterBudget) filtered = filtered.filter(p => parseFloat(p.price) <= parseFloat(filterBudget));
        if (filterGender) filtered = filtered.filter(p => p.gender === filterGender);
        setProperties(filtered);
        setActiveTab('all');
    }

    function resetFilters() {
        setFilterCity(''); setFilterBudget(''); setFilterGender('');
        setProperties(ALL_PROPERTIES);
    }

    function toggleShortlist(property) {
        setShortlist(prev =>
            prev.find(p => p.id === property.id)
                ? prev.filter(p => p.id !== property.id)
                : [...prev, property]
        );
    }

    const displayed = activeTab === 'shortlist' ? shortlist : properties;

    return (
        <div>
            <section className="hero">
                <div className="container text-center">
                    <h1>
                        <i className="bi bi-geo-alt-fill me-2 text-warning"></i>
                        Find Your Perfect PG
                        <span className="react-badge">React</span>
                    </h1>
                    <p className="mt-2" style={{color:'rgba(255,255,255,0.75)'}}>This listing is powered by a React component</p>
                </div>
            </section>

            <div className="container">
                <div className="filter-bar">
                    <div className="row g-3 align-items-end">
                        <div className="col-md-3">
                            <label className="form-label small fw-semibold text-muted mb-1">City</label>
                            <select value={filterCity} onChange={e => setFilterCity(e.target.value)}>
                                <option value="">All Cities</option>
                                {cities.map(c => <option key={c} value={c}>{c}</option>)}
                            </select>
                        </div>
                        <div className="col-md-3">
                            <label className="form-label small fw-semibold text-muted mb-1">Max Budget (₹/month)</label>
                            <input type="number" placeholder="e.g. 8000" value={filterBudget} onChange={e => setFilterBudget(e.target.value)} />
                        </div>
                        <div className="col-md-2">
                            <label className="form-label small fw-semibold text-muted mb-1">Gender</label>
                            <select value={filterGender} onChange={e => setFilterGender(e.target.value)}>
                                <option value="">All</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="both">Co-ed</option>
                            </select>
                        </div>
                        <div className="col-md-2">
                            <button className="btn-filter" onClick={applyFilters}>
                                <i className="bi bi-funnel-fill me-1"></i>Search
                            </button>
                        </div>
                        <div className="col-md-2">
                            <button className="btn-filter" onClick={resetFilters} style={{background:'#64748b'}}>
                                <i className="bi bi-x-circle me-1"></i>Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div className="container mt-4">
                <div className="border-bottom mb-4">
                    <button className={`tab-btn ${activeTab === 'all' ? 'active' : ''}`} onClick={() => setActiveTab('all')}>
                        <i className="bi bi-grid me-1"></i>All Properties ({properties.length})
                    </button>
                    <button className={`tab-btn ${activeTab === 'shortlist' ? 'active' : ''}`} onClick={() => setActiveTab('shortlist')}>
                        <i className="bi bi-bookmark-fill me-1"></i>My Shortlist ({shortlist.length})
                    </button>
                </div>

                {displayed.length > 0 ? (
                    <div className="row g-4">
                        {displayed.map(p => (
                            <PropertyCard
                                key={p.id}
                                property={p}
                                isShortlisted={!!shortlist.find(s => s.id === p.id)}
                                onToggleShortlist={toggleShortlist}
                            />
                        ))}
                    </div>
                ) : (
                    <div className="no-results">
                        <i className="bi bi-search"></i>
                        <h5>{activeTab === 'shortlist' ? 'No shortlisted properties yet' : 'No properties found'}</h5>
                        <p className="small">{activeTab === 'shortlist' ? 'Click Add to Shortlist on any card' : 'Try adjusting your filters'}</p>
                    </div>
                )}
            </div>

            <ShortlistPanel shortlist={shortlist} onRemove={(id) => setShortlist(prev => prev.filter(p => p.id !== id))} />
        </div>
    );
}

const root = ReactDOM.createRoot(document.getElementById('react-root'));
root.render(<App />);
</script>

<?php require_once 'includes/footer.php'; ?>
</body>
</html>
