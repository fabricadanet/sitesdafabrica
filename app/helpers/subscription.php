<?php
//
function getUserLatestSubscription(PDO $db, $userId) {
    $sql = "
        SELECT s.*, p.can_access_premium
        FROM subscriptions s
        LEFT JOIN plans p ON p.id = s.plan_id
        WHERE s.user_id = :user_id
        ORDER BY s.id DESC
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC); // CORRETO COM PDO
}

function userCanAccessPremium(PDO $db, $userId) {
    $subscription = getUserLatestSubscription($db, $userId);

    if (!$subscription) {
        return false;
    }

    return intval($subscription['can_access_premium']) === 1;
}

