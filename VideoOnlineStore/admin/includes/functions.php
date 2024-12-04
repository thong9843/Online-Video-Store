<?php
function getDurationInMinutes($duration) {
    list($hours, $minutes, $seconds) = explode(':', $duration);
    return $hours * 60 + $minutes;
}


function getVideosByCategory($pdo, $categoryId) {
    $stmt = $pdo->prepare("SELECT * FROM Videos v
                            JOIN VideoCategories vc ON v.video_id = vc.video_id
                            WHERE vc.category_id = ?");
    $stmt->execute([$categoryId]);
    return $stmt->fetchAll();
}



function checkCartItem($pdo, $userId, $videoId) {
    $stmt = $pdo->prepare("SELECT 1 FROM Cart WHERE user_id = ? AND video_id = ?");
    $stmt->execute([$userId, $videoId]);
    return $stmt->fetchColumn();
}


function purchaseVideos($pdo, $userId, $videoIds) {
    $stmt = $pdo->prepare("EXEC PurchaseVideos @userId = ?, @videoIds = ?");
    return $stmt->execute([$userId, $videoIds]);
}

function getVideos($conn, $searchQuery = '', $categoryId = null) {
    $sql = "SELECT v.* FROM Videos v";

    if ($categoryId !== null) {
        $sql .= " JOIN VideoCategories vc ON v.video_id = vc.video_id WHERE vc.category_id = :categoryId";
    }

    if (!empty($searchQuery)) {
        $sql .= ($categoryId !== null ? " AND" : " WHERE") . " v.title LIKE :searchQuery";
    }

    $stmt = $conn->prepare($sql);

    if ($categoryId !== null) {
        $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
    }

    if (!empty($searchQuery)) {
        $searchQuery = '%' . $searchQuery . '%';
        $stmt->bindParam(':searchQuery', $searchQuery, PDO::PARAM_STR);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserById($conn, $userId) {
    $sql = "SELECT * FROM Users WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getUserPurchaseHistory($conn, $userId) {
    $sql = "SELECT * FROM UserPurchaseHistory WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getVideoById($conn, $videoId) {
    $sql = "SELECT * FROM GetVideoDetails (:video_id)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':video_id', $videoId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}






function RemoveItemsFromCart($conn, $userId, $videoIds) {
    $sql = "EXEC RemoveItemsFromCart @userId = :userId, @videoIds = :videoIds";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':videoIds', $videoIds, PDO::PARAM_STR);
    $stmt->execute();
}

function Checkout($conn, $userId, $videoIds) {
    $sql = "EXEC Checkout @userId = :userId, @videoIds = :videoIds";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':videoIds', $videoIds, PDO::PARAM_STR);
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
    $sql = "SELECT * FROM Categories";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getLatestVideos($conn, $limit = 5) {
    $sql = "SELECT TOP (:limit) * FROM Videos WHERE is_inactive = 0 ORDER BY video_id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getVideoDetails($conn, $videoId) {
    $sql = "SELECT * FROM GetVideoDetails (:video_id)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':video_id', $videoId, PDO::PARAM_INT);
    $stmt->execute();
    
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($rows)) {
        return null; 
    }
    
    $video = [
        'video_id' => $rows[0]['video_id'],
        'title' => $rows[0]['title'],
        'price' => $rows[0]['price'],
        'description' => $rows[0]['description'],
        'image_url' => $rows[0]['image_url'],
        'type' => $rows[0]['type'],
        'episodes' => $rows[0]['episodes'],
        'duration' => $rows[0]['duration'],
        'is_video' => $rows[0]['is_video'],
        'actor_names' => [],
        'category_names' => [],
        'download_url' => $rows[0]['download_url'],
        'release_day' => $rows[0]['release_day'],
    ];
    
    foreach ($rows as $row) {
        if (!empty($row['actor_name'])) {
            $video['actor_names'][] = $row['actor_name'];
        }
        if (!empty($row['category_name'])) {
            $video['category_names'][] = $row['category_name'];
        }
    }
    
    $video['actor_names'] = array_unique($video['actor_names']);
    $video['category_names'] = array_unique($video['category_names']);
    
    return $video;
}


function videoExistsInCart($conn, $userId, $videoId) {
    $sql = "SELECT COUNT(*) FROM CartItems WHERE user_id = :userId AND video_id = :videoId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':videoId', $videoId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
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
    $sql = "EXEC UpdateCustomerInfo @userId = :userId, @newUsername = :newUsername, @newFullname = :newFullname, @newEmail = :newEmail, @newPassword = :newPassword";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':newUsername', $newUsername, PDO::PARAM_STR);
    $stmt->bindParam(':newFullname', $newFullname, PDO::PARAM_STR);
    $stmt->bindParam(':newEmail', $newEmail, PDO::PARAM_STR);
    $stmt->bindParam(':newPassword', $newPassword, PDO::PARAM_STR);
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

        $sql = "EXEC CreateUser @username = :username, @fullname = :fullname, @email = :email, @password = :password";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':fullname', $fullname, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);

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


function countUsers($conn) {
    $sql = "SELECT COUNT(*) FROM Users WHERE user_type = 'customer'"; 
    $stmt = $conn->query($sql);
    return $stmt->fetchColumn();
}

function countVideos($conn) {
    $sql = "SELECT COUNT(*) FROM Videos WHERE is_inactive = 0"; 
    $stmt = $conn->query($sql);
    return $stmt->fetchColumn();
}


function countActors($conn) {
    $sql = "SELECT COUNT(*) FROM Actors";
    $stmt = $conn->query($sql);
    return $stmt->fetchColumn();
}


function countOrders($conn) {
    $sql = "SELECT COUNT(*) FROM Orders";
    $stmt = $conn->query($sql);
    return $stmt->fetchColumn();
}

function getAllVideos($conn) {
    $sql = "SELECT * FROM dbo.GetAllVideos()"; 
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllActors($conn) {
    $sql = "EXEC GetAllActors";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}








function getActorById($conn, $actorId) {
    $sql = "SELECT * FROM Actors WHERE actor_id = :actorId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':actorId', $actorId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function AddActor($conn, $actorName) {
    try {
        $sql = "EXEC AddActor @actorName = :actorName";
        $stmt = $conn->prepare($sql);
    
        $stmt->bindParam(':actorName', $actorName, PDO::PARAM_STR);
        
        $stmt->execute();
        
        echo "Actor added successfully.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

function UpdateActor($conn, $actorId, $newActorName) {
    try {
        $sql = "EXEC UpdateActor @actorId = :actorId, @newActorName = :newActorName";
        $stmt = $conn->prepare($sql);
    
        $stmt->bindParam(':actorId', $actorId, PDO::PARAM_INT);
        $stmt->bindParam(':newActorName', $newActorName, PDO::PARAM_STR);
        
        $stmt->execute();
        
        echo "Actor update successfully.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

function DeleteActors($conn, $actorId) {
    try {
        if (empty($actorId)) {
            throw new Exception("Missing actor ID.");
        }

        $sql = "EXEC DeleteActors @actorId = :actorId";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':actorId', $actorId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Error deleting actor: SQLSTATE[{$errorInfo[0]}] - {$errorInfo[2]}");
        }
    } catch (PDOException $e) {
        error_log("Error deleting actor (PDO): " . $e->getMessage());
        return "Error deleting actor. Please try again later.";
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function getAllUsers($conn) {
    $sql = "EXEC GetAllUsers";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function UpdateCustomerInfoAdmin($conn, $userId, $newUsername, $newFullname, $newEmail, $newPassword, $newType) {
    $sql = "EXEC UpdateCustomerInfoAdmin @userId = :userId, @newUsername = :newUsername, @newFullname = :newFullname, @newEmail = :newEmail, @newPassword = :newPassword, @newType = :newType";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':newUsername', $newUsername, PDO::PARAM_STR);
    $stmt->bindParam(':newFullname', $newFullname, PDO::PARAM_STR);
    $stmt->bindParam(':newEmail', $newEmail, PDO::PARAM_STR);
    $stmt->bindParam(':newPassword', $newPassword, PDO::PARAM_STR);
	$stmt->bindParam(':newType', $newType, PDO::PARAM_STR);
    return $stmt->execute();
}

function DeleteUser($conn, $userId) {
    try {
        if (empty($userId)) {
            throw new Exception("Missing user ID.");
        }

        $sql = "EXEC DeleteUser @userId = :userId";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return true;
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Error deleting user: SQLSTATE[{$errorInfo[0]}] - {$errorInfo[2]}");
        }

    } catch (PDOException $e) {
        error_log("Error deleting user (PDO): " . $e->getMessage());
        return "Error deleting user. Please try again later.";
    } catch (Exception $e) {
        if ($e->getCode() == 16) { 
            return "User not found.";
        } else {
            return $e->getMessage();
        }
    }
}

function getAllCommentsAndRatingsFromAllVideos($conn) {
    $sql = "SELECT * FROM AllCommentsAndRatings";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllOrders($conn) {
    $sql = "SELECT * FROM AllOrders"; 
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOrderDetails($conn, $orderId) {
    $sql = "SELECT * FROM dbo.GetOrderDetails(:orderId)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':orderId', $orderId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getOrderItems($conn, $orderId) {
    $sql = "SELECT * FROM dbo.GetOrderItems(:orderId)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':orderId', $orderId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getVideoActors($conn, $videoId) {
    $sql = "SELECT a.actor_id, a.actor_name 
            FROM VideoActors va
            JOIN Actors a ON va.actor_id = a.actor_id
            WHERE va.video_id = :videoId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':videoId', $videoId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getVideoCategories($conn, $videoId) {
    $sql = "SELECT c.category_id, c.category_name 
            FROM VideoCategories vc
            JOIN Categories c ON vc.category_id = c.category_id
            WHERE vc.video_id = :videoId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':videoId', $videoId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function uploadImage($image) {
    $targetDir = "../img/"; 
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true); 
    }

    $targetFile = $targetDir . basename($image["name"]);
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    $check = getimagesize($image["tmp_name"]);
    if ($check === false) {
        throw new Exception("File is not an image.");
    }


    if (file_exists($targetFile)) {
        $originalFileName = pathinfo($image["name"], PATHINFO_FILENAME);
        $i = 1;
        while (file_exists($targetFile)) {
            $targetFile = $targetDir . $originalFileName . "_" . $i . "." . $imageFileType;
            $i++;
        }
    }

    if ($image["size"] > 5 * 1024 * 1024) {
        throw new Exception("Sorry, your file is too large.");
    }

    $allowedTypes = array("jpg", "jpeg", "png", "gif");
    if (!in_array($imageFileType, $allowedTypes)) {
        throw new Exception("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
    }

    if (move_uploaded_file($image["tmp_name"], $targetFile)) {
        $imageUrl = "img/" . basename($image["name"]);
        return $imageUrl;
    } else {
        throw new Exception("Sorry, there was an error uploading your file.");
    }
}

function AddVideoWithActors($conn, $title, $price, $description, $imageUrl, $type, $episodes, $duration, $isVideo, $actorIds, $categoryIds, $downloadUrl, $releaseDay) {
    try {
        if (empty($title) || empty($price) || empty($type)) {
            throw new Exception("Please fill in all required fields.");
        }
        
        $actorIdsString = implode(',', $actorIds);
        $categoryIdsString = implode(',', $categoryIds);

        $sql = "EXEC AddVideo 
                @title = :title, 
                @price = :price,
                @description = :description,
                @image_url = :imageUrl,
                @type = :type,
                @episodes = :episodes,
                @duration = :duration,
                @is_video = :isVideo,
                @download_url = :downloadUrl,
                @release_day = :release_day,
                @actor_ids = :actorIds,
                @category_ids = :categoryIds";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':imageUrl', $imageUrl);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':episodes', $episodes, PDO::PARAM_INT);
        $stmt->bindParam(':duration', $duration);
        $stmt->bindParam(':isVideo', $isVideo, PDO::PARAM_BOOL);
        $download_url = $_POST['download_url'];
        $stmt->bindParam(':downloadUrl', $downloadUrl);
        $stmt->bindParam(':release_day', $releaseDay);
        $stmt->bindParam(':actorIds', $actorIdsString, PDO::PARAM_STR);
        $stmt->bindParam(':categoryIds', $categoryIdsString);
        if ($stmt->execute()) {
            return true;
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Error adding video: SQLSTATE[{$errorInfo[0]}] - {$errorInfo[2]}");
        }
    } catch (PDOException $e) {
        error_log("Error adding video (PDO): " . $e->getMessage());
        return "Error adding video. Please try again later."; 
    } catch (Exception $e) {
        return $e->getMessage(); 
    }
}

function UpdateVideoWithActors($conn, $videoId, $title, $price, $description, $imageUrl, $type, $episodes, $duration, $isVideo, $actorIds, $categoryIds, $downloadUrl, $releaseDay) {
    try {
        if (empty($videoId) || empty($title) || empty($price) || empty($type)) {
            throw new Exception("Please fill in all required fields.");
        }

        $actorIdsString = is_array($actorIds) ? implode(',', $actorIds) : '';
        $categoryIdsString = is_array($categoryIds) ? implode(',', $categoryIds) : '';

        $sql = "EXEC EditVideo 
            @video_id = :videoId,
            @title = :title, 
            @price = :price,
            @description = :description,
            @image_url = :imageUrl,
            @type = :type,
            @episodes = :episodes,
            @duration = :duration,
            @is_video = :isVideo,
            @download_url = :downloadUrl,
            @release_day = :release_day,
            @actor_ids = :actorIds,
            @category_ids = :categoryIds";
        
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':videoId', $videoId, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':imageUrl', $imageUrl);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':episodes', $episodes, PDO::PARAM_INT);
        $stmt->bindParam(':duration', $duration);
        $stmt->bindParam(':isVideo', $isVideo, PDO::PARAM_BOOL);
        $stmt->bindParam(':downloadUrl', $downloadUrl);
        $stmt->bindParam(':release_day', $releaseDay);
        $stmt->bindParam(':actorIds', $actorIdsString);
        $stmt->bindParam(':categoryIds', $categoryIdsString);

        if ($stmt->execute()) {
            return true; 
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Error updating video: SQLSTATE[{$errorInfo[0]}] - {$errorInfo[2]}");
        }
    } catch (PDOException $e) {
        error_log("Error updating video (PDO): " . $e->getMessage());
        return "Error updating video. Please try again later."; 
    } catch (Exception $e) {
        return $e->getMessage();
    }
}
