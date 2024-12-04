<?php
function formatDuration($duration) {
    $dateTime = new DateTime($duration);
    return $dateTime->format('H:i:s');
}

function getVideosByCategory($pdo, $categoryId) {
    $stmt = $pdo->prepare("SELECT * FROM Videos v
                            JOIN VideoCategories vc ON v.video_id = vc.video_id
                            WHERE vc.category_id = ?");
    $stmt->execute([$categoryId]);
    return $stmt->fetchAll();
}



function checkCartItem($conn, $userId, $videoId) {
    try {
        $sql = "EXEC CheckCartItem @userId = :userId, @videoId = :videoId";
        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':videoId', $videoId, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchColumn() == 1;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}



function purchaseVideos($pdo, $userId, $videoIds) {
    $stmt = $pdo->prepare("EXEC PurchaseVideos @userId = ?, @videoIds = ?");
    return $stmt->execute([$userId, $videoIds]);
}

function getVideos($conn, $searchQuery = '', $categoryId = null) {
    try {
        $sql = "EXEC GetVideos @searchQuery = :searchQuery, @categoryId = :categoryId";
        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':searchQuery', $searchQuery, PDO::PARAM_STR);
        $stmt->bindValue(':categoryId', $categoryId, PDO::PARAM_INT | PDO::PARAM_NULL);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return [];
    }
}

function getUserById($conn, $userId) {
    try {
        $sql = "EXEC GetUserById @userId = :userId";
        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}


function getUserPurchaseHistory($conn, $userId) {
    $sql = "SELECT * FROM UserPurchaseHistory WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getVideoById($conn, $video_id) {
    $sql = "SELECT * FROM Videos WHERE video_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$video_id]);
    $video = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($video) {
        $sql = "SELECT actor_id FROM VideoActors WHERE video_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$video_id]);
        $actor_ids = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $sql = "SELECT category_id FROM VideoCategories WHERE video_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$video_id]);
        $category_ids = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $video['actor_ids'] = $actor_ids;
        $video['category_ids'] = $category_ids;
    } else {
        $video = [
            'actor_ids' => [],
            'category_ids' => []
        ];
    }

    return $video;
}




function getAllActors($conn) {
    $sql = "EXEC GetAllActors";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



function RemoveItemsFromCart($conn, $userId, $videoIds) {
    $sql = "EXEC RemoveItemsFromCart @userId = :userId, @videoIds = :videoIds";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':videoIds', $videoIds, PDO::PARAM_STR);
    $stmt->execute();
}

function Checkout($conn, $userId, $videoIds, $paymentMethod) {
    $sql = "EXEC Checkout @userId = :userId, @videoIds = :videoIds, @paymentMethod = :paymentMethod";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':videoIds', $videoIds, PDO::PARAM_STR);
    $stmt->bindParam(':paymentMethod', $paymentMethod, PDO::PARAM_STR);
    $stmt->execute();
}


function searchVideos($conn, $searchQuery) {
    $sql = "SELECT * FROM dbo.SearchVideos(:searchQuery)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':searchQuery', $searchQuery, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCategories($conn) {
    try {
        $sql = "EXEC GetCategories";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}


function getLatestVideos($conn, $limit = 5) {
    try {
        $sql = "EXEC GetLatestVideos @Limit = :limit";
        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

function getVideoDetails($conn, $videoId) {
    $sql = "SELECT * FROM dbo.GetVideoDetails(:videoId)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':videoId', $videoId, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $videoDetails = [
        'video_id' => $videoId, 
        'title' => $results[0]['title'],
        'price' => $results[0]['price'], 
        'description' => $results[0]['description'],
        'image_url' => $results[0]['image_url'],
        'type' => $results[0]['type'],
        'episodes' => $results[0]['episodes'],
        'duration' => $results[0]['duration'],
        'is_video' => $results[0]['is_video'],
        'actor_names' => array_column($results, 'actor_name'),
        'category_names' => array_column($results, 'category_name')
    ];
    return $videoDetails;
}


function videoExistsInCart($conn, $userId, $videoId) {
    try {
        $sql = "EXEC VideoExistsInCart @UserId = :userId, @VideoId = :videoId";
        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':videoId', $videoId, PDO::PARAM_INT);

        $stmt->execute();
        
        $result = $stmt->fetchColumn();
        
        return $result > 0;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}



function addToCart($conn, $userId, $videoId) {
    $sql = "EXEC AddToCart @userId = :userId, @videoId = :videoId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':videoId', $videoId, PDO::PARAM_INT);
    $stmt->execute();
}


function getCartDetails($conn, $userId) {
    $sql = "EXEC ViewCart @userId = :userId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCommentsAndRatings($conn, $videoId) {
    $sql = "EXEC GetAllCommentsAndRatings @videoId = :videoId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':videoId', $videoId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserPurchasedVideos($conn, $userId) {
    $sql = "SELECT * FROM UserPurchasedVideos WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function UpdateCustomerInfo($conn, $userId, $newUsername, $newFullname, $newEmail, $newPassword) {
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    $sql = "EXEC UpdateCustomerInfo @userId = :userId, @newUsername = :newUsername, @newFullname = :newFullname, @newEmail = :newEmail, @newPassword = :newPassword";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':newUsername', $newUsername, PDO::PARAM_STR);
    $stmt->bindParam(':newFullname', $newFullname, PDO::PARAM_STR);
    $stmt->bindParam(':newEmail', $newEmail, PDO::PARAM_STR);
    $stmt->bindParam(':newPassword', $hashedPassword, PDO::PARAM_STR);
    return $stmt->execute();
}

function CreateUser($conn, $username, $fullname, $email, $password) {
    try {
        if (empty($username) || empty($fullname) || empty($email) || empty($password)) {
            throw new Exception("Please fill in all fields.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }
        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long.");
        }
        $checkSql = "SELECT COUNT(*) FROM Users WHERE username = :username OR email = :email";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bindParam(':username', $username);
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();
        if ($checkStmt->fetchColumn() > 0) {
            throw new Exception("Username or email already exists.");
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $sql = "EXEC CreateUser @username = :username, @fullname = :fullname, @email = :email, @password = :password";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':fullname', $fullname, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

        $stmt->execute();


        return true; 
    } catch (PDOException $e) {
        error_log("Error creating user (PDO): " . $e->getMessage());
        throw new Exception("Registration failed due to a database error.");
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function userHasPurchasedVideo($conn, $userId, $videoId) {
    $sql = "SELECT COUNT(*) FROM UserPurchasedVideos WHERE user_id = :userId AND video_id = :videoId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':videoId', $videoId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

function GetAllCommentsAndRatings($conn, $videoId) {
    $sql = "EXEC GetAllCommentsAndRatings @videoId = :videoId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':videoId', $videoId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getRatingById($conn, $ratingId) {
    $sql = "SELECT * FROM Ratings WHERE rating_id = :rating_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':rating_id', $ratingId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function UpdateCommentAndRating($conn, $ratingId, $newRating, $newComment) {
    try {
        if (empty($ratingId) || empty($newRating)) {
            throw new Exception("Missing required fields.");
        }

        $checkSql = "SELECT COUNT(*) FROM Ratings WHERE rating_id = :ratingId";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bindParam(':ratingId', $ratingId, PDO::PARAM_INT);
        $checkStmt->execute();
        if ($checkStmt->fetchColumn() == 0) {
            throw new Exception("Rating not found.");
        }

        $sql = "EXEC UpdateCommentAndRating @ratingId = :ratingId, @newRating = :newRating, @newComment = :newComment";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':ratingId', $ratingId, PDO::PARAM_INT);
        $stmt->bindParam(':newRating', $newRating, PDO::PARAM_INT);
        $stmt->bindParam(':newComment', $newComment, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Error updating comment (SQLSTATE {$errorInfo[0]}): {$errorInfo[2]}");
        }
        
        return true;

    } catch (PDOException $e) {
        error_log("Error updating comment (PDO): " . $e->getMessage());
        return "Error updating comment. Please try again later.";
    } catch (Exception $e) {
        error_log("Error updating comment: " . $e->getMessage());
        return $e->getMessage();
    }
}


function DeleteCommentAndRating($conn, $ratingId) {
    try {
        if (empty($ratingId)) {
            throw new Exception("Missing rating ID.");
        }

        $checkSql = "SELECT COUNT(*) FROM Ratings WHERE rating_id = :ratingId";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bindParam(':ratingId', $ratingId, PDO::PARAM_INT);
        $checkStmt->execute();
        if ($checkStmt->fetchColumn() == 0) {
            throw new Exception("Rating not found.");
        }

        $sql = "EXEC DeleteCommentAndRating @ratingId = :ratingId";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':ratingId', $ratingId, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return true; 
    } catch (PDOException $e) {
        error_log("Error deleting comment (PDO): " . $e->getMessage());
        return false;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function AddCommentAndRating($conn, $videoId, $userId, $rating, $comment = null) {
    try {
        if (empty($videoId) || empty($userId) || empty($rating)) {
            throw new Exception("Missing required fields.");
        }

        $checkSql = "SELECT COUNT(*) FROM Ratings WHERE user_id = :userId AND video_id = :videoId";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $checkStmt->bindParam(':videoId', $videoId, PDO::PARAM_INT);
        $checkStmt->execute();
        if ($checkStmt->fetchColumn() > 0) {
            throw new Exception("You have already rated this video.");
        }

        $sql = "EXEC AddCommentAndRating @videoId = :videoId, @userId = :userId, @rating = :rating, @comment = :comment";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':videoId', $videoId, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return true; 
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Error adding comment: SQLSTATE[{$errorInfo[0]}] - {$errorInfo[2]}");
        }
    } catch (PDOException $e) {
        error_log("Error adding comment (PDO): " . $e->getMessage());
        return "Error adding comment. Please try again later."; 
    } catch (Exception $e) {
        return $e->getMessage(); 
    }
}
