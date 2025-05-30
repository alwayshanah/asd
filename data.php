<?php
session_start();

if (isset($_GET['logout'])) {
    $_SESSION = array();
    session_destroy();
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// database connection
require_once 'db_connection.php';

$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : '';
$sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'desc';
$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
$filterType = isset($_GET['type']) ? $_GET['type'] : '';
$filterFunding = isset($_GET['funding']) ? $_GET['funding'] : '';

// Step 1: Base UNION query
$baseQuery = "
    SELECT 
        p.projectId AS id,
        p.projectTitle AS title,
        'Project' AS type,
        CONCAT(p.startDate, ' to ', p.endDate) AS duration,
        p.status,
        p.yearCompleted,
        p.fieldOfStudy,
        p.sourceOfFunding,
        GROUP_CONCAT(c.collaboratorName SEPARATOR '\n') AS collaboratorName,
        p.projectId AS no
    FROM project p
    LEFT JOIN collaboration ps ON p.projectId = ps.projectId
    LEFT JOIN collaborator c ON ps.collaboratorId = c.collaboratorId
    GROUP BY p.projectId

    UNION ALL

    SELECT 
        s.studyId AS id,
        s.studyTitle AS title,
        'Study' AS type,
        CONCAT(s.startDate, ' to ', s.endDate) AS duration,
        s.status,
        s.yearCompleted,
        s.fieldOfStudy,
        s.sourceOfFunding,
        GROUP_CONCAT(c2.collaboratorName SEPARATOR '\n') AS collaboratorName,
        s.studyId AS no
    FROM study s
    LEFT JOIN collaboration ps2 ON s.studyId = ps2.studyId
    LEFT JOIN collaborator c2 ON ps2.collaboratorId = c2.collaboratorId
    GROUP BY s.studyId
";

// Execute query to get all records
$result = $conn->query($baseQuery);
$records = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
} else {
    die("Query failed: " . $conn->error);
}

$filteredRecords = $records;

if (!empty($searchQuery)) {
    $filteredRecords = array_filter($filteredRecords, function($record) use ($searchQuery) {
        foreach (['no', 'title', 'type', 'status', 'duration', 'fieldOfStudy', 'sourceOfFunding', 'collaboratorName', 'yearCompleted'] as $field) {
            if (isset($record[$field]) && stripos($record[$field], $searchQuery) !== false) {
                return true;
            }
        }
        return false;
    });
}

if (!empty($filterStatus)) {
    $filteredRecords = array_filter($filteredRecords, function($record) use ($filterStatus) {
        return strcasecmp($record['status'], $filterStatus) === 0;
    });
}

if (!empty($filterType)) {
    $filteredRecords = array_filter($filteredRecords, function($record) use ($filterType) {
        return strcasecmp($record['type'], $filterType) === 0;
    });
}

if (!empty($filterFunding)) {
    $filteredRecords = array_filter($filteredRecords, function($record) use ($filterFunding) {
        return strcasecmp($record['sourceOfFunding'], $filterFunding) === 0;
    });
}

if (!empty($sortBy)) {
    usort($filteredRecords, function($a, $b) use ($sortBy, $sortOrder) {
        $valueA = $a[$sortBy] ?? '';
        $valueB = $b[$sortBy] ?? '';

        if ($sortBy === 'yearCompleted') {
            $valueA = (int)$valueA;
            $valueB = (int)$valueB;
        } elseif ($sortBy === 'duration') {
            $valueA = strtolower($valueA);
            $valueB = strtolower($valueB);
        }

        if ($sortOrder === 'asc') {
            return $valueA <=> $valueB;
        } else {
            return $valueB <=> $valueA;
        }
    });
}

// Step 4: Helper functions
function hasRecordContent($record) {
    foreach ($record as $key => $value) {
        if ($key !== 'no' && !empty(trim($value))) {
            return true;
        }
    }
    return false;
}

function getFirstTeamMember($teamString) {
    $teamMembers = preg_split('/\r\n|\r|\n/', $teamString);
    return trim($teamMembers[0]);
}

function formatTeamMembersList($teamString) {
    $teamMembers = preg_split('/\r\n|\r|\n/', $teamString);
    $teamMembers = array_filter(array_map('trim', $teamMembers));
    return implode('<br>', $teamMembers);
}

function getUniqueValues($records, $field) {
    $values = array_map(fn($record) => $record[$field], $records);
    return array_unique($values);
}

// Step 5: Filter option values for dropdowns
$statusOptions = getUniqueValues($records, 'status');
$typeOptions = getUniqueValues($records, 'type');
$fundingOptions = getUniqueValues($records, 'sourceOfFunding');

// Step 6: URL builder for filters
function buildFilterUrl($params) {
    $currentParams = $_GET;
    foreach ($params as $key => $value) {
        if ($value === null) {
            unset($currentParams[$key]);
        } else {
            $currentParams[$key] = $value;
        }
    }
    return '?' . http_build_query($currentParams);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Tracker System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            background-image: url('background-texture.jpg');
            background-blend-mode: overlay;
        }
        
        header {
            background-color: #6b2626;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin-right: 10px;
        }
        
        .title {
            text-transform: uppercase;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
            line-height: 1.2;
        }
        
        .user-info {
            text-align: right;
            font-size: 14px;
        }
        
        .admin-tag {
            font-size: 12px;
            opacity: 0.8;
        }
        
        main {
            padding: 20px;
        }
        
        h1 {
            margin-top: 0;
            color: #333;
        }
        
        .search-filter-container {
            display: flex;
            margin-bottom: 20px;
        }
        
        .search-container {
            position: relative;
            margin-right: 10px;
        }
        
        .search-input {
            padding: 8px 30px 8px 10px;
            border: 1px solid #ccc;
            border-radius: 20px;
            width: 200px;
        }
        
        .search-icon {
            position: absolute;
            right: 10px;
            top: 45%;
            transform: translateY(-50%);
            color: #666;
        }
        
        .button {
            padding: 5px 15px;
            background-color: #6b2626;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            display: flex;
            align-items: center;
            text-decoration: none;
            min-width: 65px;
            height: 30px;
            box-sizing: border-box;
            font-size: 15px;
        }
        
        .button-icon {
            margin-left: 5px;
            text-decoration: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #a3a3a3;
            color: white;
            font-weight: normal;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            right: 0;
            background-color: #6b2626;
            color: white;
            padding: 8px 15px 8px 10000px;
            border-top-left-radius: 4px;
        }
        
        .logout-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .logout-icon {
            width: 90px;
        }

        .no-results {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            margin-top: 20px;
        }

        .highlight {
            background-color: #ffffa0;
            padding: 2px;
            border-radius: 2px;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
        }
        
        .action-button {
            background: none;
            border: none;
            cursor: pointer;
            color: #6b2626;
            padding: 3px;
            border-radius: 4px;
            transition: background-color 0.2s ease;
            text-decoration: none;
        }
        
        .action-button:hover {
            background-color: #f0f0f0;
        }
        
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 200px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 4px;
        }
        
        .dropdown-content a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background-color 0.2s ease;
        }
        
        .dropdown-content a:hover {
            background-color: #E1E2E4;
        }
        
        .show-dropdown {
            display: block;
        }
        
        .active-dropdown {
            background-color: #552424;
        }

        .active-sort {
            background-color: #E1E2E4;
        }
        
        .filter-section {
            padding: 12px 16px;
            border-bottom: 1px solid #e1e1e1;
        }
        
        .filter-section:last-child {
            border-bottom: none;
        }
        
        .filter-section h3 {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: #666;
        }
        
        .filter-option {
            margin-bottom: 5px;
        }
        
        .filter-option a {
            display: flex;
            align-items: center;
            padding: 5px 0;
            color: #333;
            text-decoration: none;
            font-size: 14px;
        }
        
        .filter-option a:hover {
            color: #6b2626;
        }
        
        .filter-indicator {
            width: 12px;
            height: 12px;
            border: 1px solid #ccc;
            border-radius: 2px;
            margin-right: 8px;
            display: inline-block;
        }
        
        .filter-selected .filter-indicator {
            background-color: #6b2626;
            border-color: #6b2626;
        }
        
        .filter-badge {
            display: inline-block;
            background-color: #6b2626;
            color: white;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 5px;
        }
        
        .sort-indicator {
            display: none;
        }
        
        .team-dropdown {
            position: relative;
            cursor: pointer;
        }
        
        .team-dropdown .first-member {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 3px;
            border-radius: 3px;
            transition: background-color 0.2s;
            text-decoration: underline;
        }
        
        .team-dropdown .first-member:hover {
            text-decoration: underline;
            color: #6b2626;
        }
        
        .team-dropdown .team-members {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 250px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            padding: 10px 15px;
            border-radius: 4px;
            z-index: 10;
            left: 0;
            top: 100%;
            margin-top: 3px;
            line-height: 1.5;
        }
        
        .dropdown-icon {
            font-size: 12px;
            margin-left: 5px;
        }
        
        .active-team-dropdown .team-members {
            display: block;
        }
        
        .active-team-dropdown .first-member {
            background-color: #f0f0f0;
        }

        .info-icon {
            display: inline-flex;
            text-align: center;
            justify-content: center;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            border: 2px solid #6b2626;
            color: #6b2626;
            font-weight: bold;
            font-style: italic;
            font-family: serif;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        
        .info-icon:hover {
            background-color: #6b2626;
            color: white;
        }
        
        .active-filters {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        
        .filter-tag {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 5px 12px;
            margin-right: 10px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            font-size: 13px;
        }
        
        .filter-tag-remove {
            margin-left: 5px;
            color: #999;
            font-weight: bold;
            cursor: pointer;
        }
        
        .filter-tag-remove:hover {
            color: #6b2626;
        }
        
        .clear-all-filters {
            color: #6b2626;
            text-decoration: none;
            font-size: 13px;
            margin-left: auto;
            padding: 5px;
        }
        
        .clear-all-filters:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        function checkForEnter(event) {
            if (event.keyCode === 13) {
                document.getElementById('searchForm').submit();
                event.preventDefault();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const sortDropdownBtn = document.querySelector('.sort-dropdown .button');
            const sortDropdownContent = document.querySelector('.sort-dropdown .dropdown-content');
            
            if (sortDropdownBtn) {
                sortDropdownBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    sortDropdownContent.classList.toggle('show-dropdown');
                    
                    const filterDropdownContent = document.querySelector('.filter-dropdown .dropdown-content');
                    if (filterDropdownContent && filterDropdownContent.classList.contains('show-dropdown')) {
                        filterDropdownContent.classList.remove('show-dropdown');
                    }
                });
            }
            
            const filterDropdownBtn = document.querySelector('.filter-dropdown .button');
            const filterDropdownContent = document.querySelector('.filter-dropdown .dropdown-content');
            
            if (filterDropdownBtn) {
                filterDropdownBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    filterDropdownContent.classList.toggle('show-dropdown');
                    
                    if (sortDropdownContent && sortDropdownContent.classList.contains('show-dropdown')) {
                        sortDropdownContent.classList.remove('show-dropdown');
                    }
                });
            }

            window.addEventListener('click', function(e) {
                if (!e.target.matches('.dropdown .button') && 
                    !e.target.matches('.button-icon')) {
                    
                    const dropdowns = document.querySelectorAll('.dropdown-content');
                    dropdowns.forEach(function(dropdown) {
                        if (dropdown.classList.contains('show-dropdown')) {
                            dropdown.classList.remove('show-dropdown');
                        }
                    });
                }
            });
            
            const teamDropdowns = document.querySelectorAll('.team-dropdown');
            teamDropdowns.forEach(dropdown => {
                dropdown.querySelector('.first-member').addEventListener('click', function(e) {
                    e.stopPropagation();

                    teamDropdowns.forEach(otherDropdown => {
                        if (otherDropdown !== dropdown) {
                            otherDropdown.classList.remove('active-team-dropdown');
                        }
                    });
                    
                    dropdown.classList.toggle('active-team-dropdown');
                });
            });

            document.addEventListener('click', function(e) {
                const isTeamDropdown = e.target.closest('.team-dropdown');
                if (!isTeamDropdown) {
                    teamDropdowns.forEach(dropdown => {
                        dropdown.classList.remove('active-team-dropdown');
                    });
                }
            });
        });
    </script>
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="images/CIC_LOGO1.png" alt="Logo" class="logo">
            <div class="title">
                RESEARCH<br>TRACKER SYSTEM
            </div>
        </div>
        <div class="user-info">
            Cedric Y. Tiil<br>
            <span class="admin-tag">ADMIN</span>
        </div>
    </header>
    
    <main>
        <h1>Research Records</h1>

        <div class="search-filter-container">
            <form id="searchForm" method="GET" action="" class="search-container">
                <input type="text" name="search" placeholder="Search" class="search-input" 
                       value="<?php echo htmlspecialchars($searchQuery); ?>" 
                       onkeypress="checkForEnter(event)">
                <button type="submit" class="search-icon" style="background:none; border:none; cursor:pointer;">🔍︎</button>
                <?php if (!empty($sortBy)): ?>
                    <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($sortBy); ?>">
                    <input type="hidden" name="sort_order" value="<?php echo htmlspecialchars($sortOrder); ?>">
                <?php endif; ?>
                <?php if (!empty($filterStatus)): ?>
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($filterStatus); ?>">
                <?php endif; ?>
                <?php if (!empty($filterType)): ?>
                    <input type="hidden" name="type" value="<?php echo htmlspecialchars($filterType); ?>">
                <?php endif; ?>
                <?php if (!empty($filterFunding)): ?>
                    <input type="hidden" name="funding" value="<?php echo htmlspecialchars($filterFunding); ?>">
                <?php endif; ?>
            </form>
            
            <div class="dropdown sort-dropdown">
                <button class="button <?php echo (!empty($sortBy)) ? 'active-dropdown' : ''; ?>">
                    Sort
                    <span class="button-icon"></span>
                </button>

                <div class="dropdown-content">
                    <a href="<?php echo buildFilterUrl(['sort_by' => 'yearCompleted', 'sort_order' => 'desc']); ?>" 
                       class="<?php echo ($sortBy == 'yearCompleted' && $sortOrder == 'desc') ? 'active-sort' : ''; ?>">
                        Year Completed (Newest First)
                    </a>

                    <a href="<?php echo buildFilterUrl(['sort_by' => 'yearCompleted', 'sort_order' => 'asc']); ?>" 
                       class="<?php echo ($sortBy == 'yearCompleted' && $sortOrder == 'asc') ? 'active-sort' : ''; ?>">
                        Year Completed (Oldest First)
                    </a>

                    <a href="<?php echo buildFilterUrl(['sort_by' => 'duration', 'sort_order' => 'desc']); ?>" 
                       class="<?php echo ($sortBy == 'duration' && $sortOrder == 'desc') ? 'active-sort' : ''; ?>">
                        Duration (Longest First)
                    </a>

                    <a href="<?php echo buildFilterUrl(['sort_by' => 'duration', 'sort_order' => 'asc']); ?>" 
                       class="<?php echo ($sortBy == 'duration' && $sortOrder == 'asc') ? 'active-sort' : ''; ?>">
                        Duration (Shortest First)
                    </a>
                </div>
            </div>
            
            <div class="dropdown filter-dropdown">
                <button class="button <?php echo (!empty($filterStatus) || !empty($filterType) || !empty($filterFunding)) ? 'active-dropdown' : ''; ?>">
                    Filter
                    <span class="button-icon"></span>
                    <?php if (!empty($filterStatus) || !empty($filterType) || !empty($filterFunding)): ?>
                        <span class="filter-badge"><?php 
                            $activeFilters = 0;
                            if (!empty($filterStatus)) $activeFilters++;
                            if (!empty($filterType)) $activeFilters++;
                            if (!empty($filterFunding)) $activeFilters++;
                            echo $activeFilters;
                        ?></span>
                    <?php endif; ?>
                </button>

                <div class="dropdown-content">
                    <div class="filter-section">
                        <h3>Status</h3>
                        <?php foreach ($statusOptions as $status): ?>
                        <div class="filter-option <?php echo ($filterStatus === $status) ? 'filter-selected' : ''; ?>">
                            <a href="<?php echo buildFilterUrl(['status' => ($filterStatus === $status) ? null : $status]); ?>">
                                <span class="filter-indicator"></span>
                                <?php echo htmlspecialchars($status); ?>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="filter-section">
                        <h3>Research Type</h3>
                        <?php foreach ($typeOptions as $type): ?>
                        <div class="filter-option <?php echo ($filterType === $type) ? 'filter-selected' : ''; ?>">
                            <a href="<?php echo buildFilterUrl(['type' => ($filterType === $type) ? null : $type]); ?>">
                                <span class="filter-indicator"></span>
                                <?php echo htmlspecialchars($type); ?>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="filter-section">
                        <h3>Source of Funding</h3>
                        <?php foreach ($fundingOptions as $funding): ?>
                        <div class="filter-option <?php echo ($filterFunding === $funding) ? 'filter-selected' : ''; ?>">
                            <a href="<?php echo buildFilterUrl(['funding' => ($filterFunding === $funding) ? null : $funding]); ?>">
                                <span class="filter-indicator"></span>
                                <?php echo htmlspecialchars($funding); ?>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (!empty($filterStatus) || !empty($filterType) || !empty($filterFunding)): ?>

                    <?php endif; ?>
                </div>
            </div>
            
            <a href="AddResearchStaff.php" class="button">
                Add
                <span class="button-icon"></span>
            </a>
            
            <div style="flex-grow: 1;"></div>
            
            <button class="button manage-accounts">
                Manage Accounts
                <span class="button-icon">▼</span>
            </button>
        </div>
        
        <?php if (!empty($filterStatus) || !empty($filterType) || !empty($filterFunding)): ?>
        <div class="active-filters">
            <?php if (!empty($filterStatus)): ?>
            <div class="filter-tag">
                Status: <?php echo htmlspecialchars($filterStatus); ?>
                <a href="<?php echo buildFilterUrl(['status' => null]); ?>" class="filter-tag-remove">×</a>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($filterType)): ?>
            <div class="filter-tag">
                Type: <?php echo htmlspecialchars($filterType); ?>
                <a href="<?php echo buildFilterUrl(['type' => null]); ?>" class="filter-tag-remove">×</a>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($filterFunding)): ?>
            <div class="filter-tag">
                Funding: <?php echo htmlspecialchars($filterFunding); ?>
                <a href="<?php echo buildFilterUrl(['funding' => null]); ?>" class="filter-tag-remove">×</a>
            </div>
            <?php endif; ?>
            
            <a href="<?php echo buildFilterUrl(['status' => null, 'type' => null, 'funding' => null]); ?>" class="clear-all-filters">
                Clear all filters
            </a>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($searchQuery)): ?>
        <div style="margin-bottom: 15px;">
            <p>Search results for: <strong><?php echo htmlspecialchars($searchQuery); ?></strong> 
               (<?php echo count($filteredRecords); ?> result<?php echo count($filteredRecords) != 1 ? 's' : ''; ?> found)
               <a href="<?php echo buildFilterUrl(['search' => null]); ?>" style="margin-left: 10px; color: #6b2626;">Clear search</a>
            </p>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($sortBy)): ?>
        <div style="margin-bottom: 15px;">
            <p>Records sorted by: 
                <strong>
                    <?php 
                    if ($sortBy == 'yearCompleted') {
                        echo 'Year Completed (' . ($sortOrder == 'desc' ? 'Newest First' : 'Oldest First') . ')';
                        } elseif ($sortBy == 'duration') {
                        echo 'Duration (' . ($sortOrder == 'desc' ? 'Longest First' : 'Shortest First') . ')';
                    } 
                    ?>
                </strong>
                <a href="<?php echo buildFilterUrl(['sort_by' => null, 'sort_order' => null]); ?>" style="margin-left: 10px; color: #6b2626;">Clear sort</a>
            </p>
        </div>
        <?php endif; ?>
        
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Duration</th>
                    <th>Team</th>
                    <th>Source of Funding</th>
                    <th>Collaborator</th>
                    <th>Field of Study</th>
                    <th>Status</th>
                    <th>Year Completed</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($filteredRecords) > 0): ?>
                    <?php foreach ($filteredRecords as $record): ?>
                    <tr>
                        <td><?php echo $record['no']; ?></td>
                        <td><?php 
                            if (!empty($searchQuery) && !empty($record['title'])) {
                                echo preg_replace('/(' . preg_quote($searchQuery, '/') . ')/i', '<span class="highlight">$1</span>', $record['title']);
                            } else {
                                echo $record['title'];
                            }
                        ?></td>
                        <td><?php 
                            if (!empty($searchQuery) && !empty($record['type'])) {
                                echo preg_replace('/(' . preg_quote($searchQuery, '/') . ')/i', '<span class="highlight">$1</span>', $record['type']);
                            } else {
                                echo $record['type'];
                            }
                        ?></td>
                        <td><?php 
                            if (!empty($searchQuery) && !empty($record['duration'])) {
                                echo preg_replace('/(' . preg_quote($searchQuery, '/') . ')/i', '<span class="highlight">$1</span>', $record['duration']);
                            } else {
                                echo $record['duration'];
                            }
                        ?></td>
                        <td>
                            <?php if (!empty($record['team'])): ?>
                                <div class="team-dropdown" id="team-<?php echo $record['no']; ?>">
                                    <div class="first-member">
                                        <?php 
                                            $firstMember = getFirstTeamMember($record['team']);
                                            if (!empty($searchQuery)) {
                                                echo preg_replace('/(' . preg_quote($searchQuery, '/') . ')/i', '<span class="highlight">$1</span>', $firstMember);
                                            } else {
                                                echo $firstMember;
                                            }
                                        ?>
                                        <span class="dropdown-icon"></span>
                                    </div>
                                    <div class="team-members">
                                        <?php 
                                            $teamList = formatTeamMembersList($record['team']);
                                            if (!empty($searchQuery)) {
                                                echo preg_replace('/(' . preg_quote($searchQuery, '/') . ')/i', '<span class="highlight">$1</span>', $teamList);
                                            } else {
                                                echo $teamList;
                                            }
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php 
                            if (!empty($searchQuery) && !empty($record['sourceOfFunding'])) {
                                echo preg_replace('/(' . preg_quote($searchQuery, '/') . ')/i', '<span class="highlight">$1</span>', $record['sourceOfFunding']);
                            } else {
                                echo $record['sourceOfFunding'];
                            }
                        ?></td>
                        <td><?php
        if (!empty($searchQuery) && !empty($record['collaboratorName'])) {
            echo preg_replace('/(' . preg_quote($searchQuery, '/') . ')/i', '<span class="highlight">$1</span>', nl2br($record['collaboratorName']));
        } elseif (empty($record['collaboratorName'])) {
            echo '';
        } else {
            echo nl2br($record['collaboratorName']);
        }
    ?>
</td>
                        <td><?php 
                            if (!empty($searchQuery) && !empty($record['fieldOfStudy'])) {
                                echo preg_replace('/(' . preg_quote($searchQuery, '/') . ')/i', '<span class="highlight">$1</span>', $record['fieldOfStudy']);
                            } else {
                                echo $record['fieldOfStudy'];
                            }
                        ?></td>
                        <td><?php 
                            if (!empty($searchQuery) && !empty($record['status'])) {
                                echo preg_replace('/(' . preg_quote($searchQuery, '/') . ')/i', '<span class="highlight">$1</span>', $record['status']);
                            } else {
                                echo $record['status'];
                            }
                        ?></td>
                        <td><?php 
                            if (!empty($searchQuery) && !empty($record['yearCompleted'])) {
                                echo preg_replace('/(' . preg_quote($searchQuery, '/') . ')/i', '<span class="highlight">$1</span>', $record['yearCompleted']);
                            } else {
                                echo $record['yearCompleted'];
                            }
                        ?></td>
                        <td>
                            <?php if (hasRecordContent($record)): ?>
                            <div class="action-buttons">
                                <a href="view_details.php?id=<?php echo $record['no']; ?>" class="info-icon" title="More Details">
                                    i
                                </a>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" class="no-results">No records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
    
    <div class="footer">
    <a href="?logout=1" class="logout-link">
        <span class="logout-icon">↪ Log Out</span>
    </a>
</div>
</body>
</html>