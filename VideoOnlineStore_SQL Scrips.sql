Create database VideoOnlineStore

-- Create Actors table
CREATE TABLE Actors (
    actor_id INT PRIMARY KEY IDENTITY(1,1),  
    actor_name NVARCHAR(100) NOT NULL
);

-- Create Categories table
CREATE TABLE Categories (
    category_id INT PRIMARY KEY IDENTITY(1,1),
    category_name NVARCHAR(100) NOT NULL
);

-- Create Users table
CREATE TABLE Users (
    user_id INT PRIMARY KEY IDENTITY(1,1),
    username NVARCHAR(100) NOT NULL UNIQUE,
	fullname NVARCHAR(100) NOT NULL,
    email NVARCHAR(100) NOT NULL UNIQUE,
    password NVARCHAR(255) NOT NULL,
    user_type NVARCHAR(50) NOT NULL DEFAULT 'customer',
);

-- Create Videos table
CREATE TABLE Videos (
    video_id INT PRIMARY KEY IDENTITY(1,1),
    title NVARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    is_video BIT NOT NULL, 
    image_url NVARCHAR(255),
    description text,
    type NVARCHAR(50) NOT NULL,
    episodes INT,           
    duration time(7),
	is_inactive BIT DEFAULT 0,
	download_url NVARCHAR(255),
	release_day DATE
);

-- Create VideoActors table
CREATE TABLE VideoActors (
    video_id INT,
    actor_id INT,
    PRIMARY KEY (video_id, actor_id),
    FOREIGN KEY (video_id) REFERENCES Videos(video_id),
    FOREIGN KEY (actor_id) REFERENCES Actors(actor_id)
);

-- Create VideoCategories table
CREATE TABLE VideoCategories (
    video_id INT,
    category_id INT,
    PRIMARY KEY (video_id, category_id),
    FOREIGN KEY (video_id) REFERENCES Videos(video_id),
    FOREIGN KEY (category_id) REFERENCES Categories(category_id)
);

-- Create Ratings table
CREATE TABLE Ratings (
    rating_id INT PRIMARY KEY IDENTITY(1,1),
    video_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment text,
    FOREIGN KEY (video_id) REFERENCES Videos(video_id),
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

-- Create CartItems table
CREATE TABLE CartItems (
    cart_item_id INT PRIMARY KEY IDENTITY(1,1),
    user_id INT NOT NULL, 
    video_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (video_id) REFERENCES Videos(video_id),   

    UNIQUE (user_id, video_id)
);

-- Create Orders table
CREATE TABLE Orders (
    order_id INT PRIMARY KEY IDENTITY(1,1),
    user_id INT NOT NULL,
    order_date DATETIME2 DEFAULT GETDATE(),
    total_amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);




-- Create OrderItems table 
CREATE TABLE OrderItems (
    order_item_id INT PRIMARY KEY IDENTITY(1,1),
    order_id INT NOT NULL,
    video_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES Orders(order_id),
    FOREIGN KEY (video_id) REFERENCES Videos(video_id)
);

-- Create PaymentHistory table
CREATE TABLE PaymentHistory (
    payment_id INT PRIMARY KEY IDENTITY(1,1),
    order_id INT NOT NULL UNIQUE,
    payment_date DATETIME2 DEFAULT GETDATE(),
    payment_method NVARCHAR(50),
    payment_status NVARCHAR(50),
    FOREIGN KEY (order_id) REFERENCES Orders(order_id)
);














----INDEX
Create index IX_Videos_title on [dbo].[Videos] (title)











-----------------------------------------------------------------------Stored Procedures---------------------------------------------------------------------------------
-----------------------------------------------------------------------User:---------------------------------------------------------------------------------
--Signup
CREATE PROCEDURE CreateUser
    @username NVARCHAR(100),
    @fullname NVARCHAR(100),
    @email NVARCHAR(100),
    @password nvarchar(255) 
AS
BEGIN
    BEGIN TRANSACTION;
        BEGIN TRY
            INSERT INTO Users (username, fullname, email, password)
            VALUES (@username, @fullname, @email, @password);
            COMMIT TRANSACTION;
        END TRY
        BEGIN CATCH
            ROLLBACK TRANSACTION;
            THROW;
        END CATCH
END;

--UpdateCustomerInfo
CREATE PROCEDURE UpdateCustomerInfo
    @userId INT,
    @newUsername NVARCHAR(100) = NULL,  
	@newFullname NVARCHAR(100) = NULL,  
    @newEmail NVARCHAR(100) = NULL,
	@newPassword NVARCHAR(100) = NULL
AS
BEGIN
    UPDATE Users
    SET 
        username = COALESCE(@newUsername, username), 
		fullname = COALESCE(@newFullname, fullname), 
        email = COALESCE(@newEmail, email),
		password = COALESCE(@newPassword, password)
    WHERE user_id = @userId;
END;

--UpdateCustomerInfo(For Admin)
CREATE PROCEDURE UpdateCustomerInfoAdmin
    @userId INT,
    @newUsername NVARCHAR(100) = NULL,  
	@newFullname NVARCHAR(100) = NULL,  
    @newEmail NVARCHAR(100) = NULL,
	@newPassword NVARCHAR(MAX) = NULL,
	@newType nvarchar(50) = NULL
AS
BEGIN
    UPDATE Users
    SET 
        username = COALESCE(@newUsername, username), 
		fullname = COALESCE(@newFullname, fullname), 
        email = COALESCE(@newEmail, email),
		password = COALESCE(@newPassword, password),
		user_type = COALESCE(@newType, user_type)
    WHERE user_id = @userId;
END;

--------Delete User
CREATE PROCEDURE DeleteUser
    @userId INT
AS
BEGIN
    IF NOT EXISTS (SELECT 1 FROM Users WHERE user_id = @userId)
    BEGIN
        RAISERROR('User not found!', 16, 1);
        RETURN;
    END

    BEGIN TRANSACTION;

    BEGIN TRY
        DELETE FROM Ratings WHERE user_id = @userId;

        DELETE FROM CartItems WHERE user_id = @userId;

        DELETE FROM OrderItems WHERE order_id IN (SELECT order_id FROM Orders WHERE user_id = @userId);
        DELETE FROM Orders WHERE user_id = @userId;

        DELETE FROM Users WHERE user_id = @userId;

        COMMIT TRANSACTION;
    END TRY
    BEGIN CATCH
        ROLLBACK TRANSACTION;
        THROW;
    END CATCH
END;

----------See All user
CREATE PROCEDURE GetAllUsers
AS
BEGIN
    SELECT user_id, username, fullname, email, user_type
    FROM Users;
END;

--------------GetUserById
CREATE PROCEDURE GetUserById
    @userId INT
AS
BEGIN
    SELECT * FROM Users WHERE user_id = @userId;
END



------------------------------------------------------------------------Cart and order
--AddToCart
CREATE PROCEDURE AddToCart @userId INT, @videoId INT
AS
BEGIN
    IF NOT EXISTS (SELECT 1 FROM CartItems WHERE user_id = @userId AND video_id = @videoId)
    BEGIN
        INSERT INTO CartItems (user_id, video_id)
        VALUES (@userId, @videoId);
    END
END;

--ViewCart
CREATE PROCEDURE ViewCart @userId INT
AS
BEGIN
    SELECT v.video_id, v.title, v.price, v.image_url
    FROM CartItems ci
    JOIN Videos v ON ci.video_id = v.video_id
    WHERE ci.user_id = @userId;
END;

--EmptyCart
CREATE PROCEDURE EmptyCart @userId INT
AS
BEGIN
    DELETE FROM CartItems WHERE user_id = @userId;
END;

--RemoveItemsFromCart
CREATE PROCEDURE RemoveItemsFromCart @userId INT, @videoIds NVARCHAR(MAX)
AS
BEGIN
    DELETE FROM CartItems
    WHERE user_id = @userId AND video_id IN (SELECT value FROM STRING_SPLIT(@videoIds, ','));
END;

--Checkout
CREATE PROCEDURE Checkout 
    @userId INT, 
    @videoIds NVARCHAR(MAX),
    @paymentMethod NVARCHAR(50)
AS
BEGIN
    BEGIN TRANSACTION;

    DECLARE @totalAmount DECIMAL(10, 2);
    SELECT @totalAmount = SUM(v.price) 
    FROM Videos v
    WHERE v.video_id IN (SELECT value FROM STRING_SPLIT(@videoIds, ','));

    DECLARE @orderId INT;
    INSERT INTO Orders (user_id, total_amount) VALUES (@userId, @totalAmount);
    SET @orderId = SCOPE_IDENTITY();

    INSERT INTO OrderItems (order_id, video_id, price)
    SELECT @orderId, s.value, v.price
    FROM STRING_SPLIT(@videoIds, ',') s 
    JOIN Videos v ON s.value = v.video_id; 

    DELETE FROM CartItems
    WHERE user_id = @userId AND video_id IN (SELECT value FROM STRING_SPLIT(@videoIds, ','));

    -- Insert into PaymentHistory table
    INSERT INTO PaymentHistory (order_id, payment_date, payment_method, payment_status) 
    VALUES (@orderId, GETDATE(), @paymentMethod, 'Completed');

    COMMIT TRANSACTION;
END;


---------------check cart item-----------------
CREATE PROCEDURE CheckCartItem
    @userId INT,
    @videoId INT
AS
BEGIN
    SET NOCOUNT ON;

    SELECT CASE 
        WHEN EXISTS (SELECT 1 FROM Cart WHERE user_id = @userId AND video_id = @videoId)
        THEN 1
        ELSE 0
    END AS ItemExists;
END


-------------VideoExistsInCart
CREATE PROCEDURE VideoExistsInCart
    @UserId INT,
    @VideoId INT
AS
BEGIN
    IF EXISTS (
        SELECT 1
        FROM CartItems
        WHERE user_id = @UserId AND video_id = @VideoId
    )
    BEGIN
        SELECT 1 AS ExistsInCart;
    END
    ELSE
    BEGIN
        SELECT 0 AS ExistsInCart;
    END
END

----------delete order
CREATE PROCEDURE DeleteOrder
    @orderId INT
AS
BEGIN
    SET NOCOUNT ON;

    BEGIN TRY
        IF NOT EXISTS (SELECT 1 FROM Orders WHERE order_id = @orderId)
        BEGIN
            RAISERROR('Order not found!', 16, 1);
            RETURN;
        END

        BEGIN TRANSACTION;

		DELETE FROM PaymentHistory WHERE order_id = @orderId;

        DELETE FROM OrderItems WHERE order_id = @orderId;

        DELETE FROM Orders WHERE order_id = @orderId;

        COMMIT TRANSACTION; 
    END TRY
    BEGIN CATCH
        ROLLBACK TRANSACTION; 
        DECLARE @ErrorMessage NVARCHAR(4000) = ERROR_MESSAGE();
        RAISERROR(@ErrorMessage, 16, 1);
    END CATCH
END;



--------------------------------------------------------------------------Video---------------------------------------
----------add video with actor
CREATE PROCEDURE AddVideo
    @title NVARCHAR(255),
    @price DECIMAL(10, 2),
    @description TEXT,
    @type NVARCHAR(50),
    @episodes INT = NULL,
    @duration time(7) = NULL,
    @is_video BIT,
    @download_url NVARCHAR(255),
    @image_url NVARCHAR(255),
	@release_day DATE = NULL,
    @actor_ids NVARCHAR(MAX),
    @category_ids NVARCHAR(MAX)
AS
BEGIN
    DECLARE @video_id INT;
    
    INSERT INTO Videos (title, price, description, type, episodes, duration, is_video, download_url, image_url, release_day, is_inactive)
    VALUES (@title, @price, @description, @type, @episodes, @duration, @is_video, @download_url, @image_url, @release_day, 0);
    
    SET @video_id = SCOPE_IDENTITY();
    
    IF @actor_ids IS NOT NULL
    BEGIN
        DECLARE @actorId INT;
        DECLARE actor_cursor CURSOR FOR SELECT value FROM STRING_SPLIT(@actor_ids, ',');
        OPEN actor_cursor;
        FETCH NEXT FROM actor_cursor INTO @actorId;
        WHILE @@FETCH_STATUS = 0
        BEGIN
            INSERT INTO VideoActors (video_id, actor_id) VALUES (@video_id, @actorId);
            FETCH NEXT FROM actor_cursor INTO @actorId;
        END
        CLOSE actor_cursor;
        DEALLOCATE actor_cursor;
    END
    
    IF @category_ids IS NOT NULL
    BEGIN
        DECLARE @categoryId INT;
        DECLARE category_cursor CURSOR FOR SELECT value FROM STRING_SPLIT(@category_ids, ',');
        OPEN category_cursor;
        FETCH NEXT FROM category_cursor INTO @categoryId;
        WHILE @@FETCH_STATUS = 0
        BEGIN
            INSERT INTO VideoCategories (video_id, category_id) VALUES (@video_id, @categoryId);
            FETCH NEXT FROM category_cursor INTO @categoryId;
        END
        CLOSE category_cursor;
        DEALLOCATE category_cursor;
    END
END

--------------getCategories
CREATE PROCEDURE GetCategories
AS
BEGIN
    SELECT * FROM Categories;
END

-----------------GetLatestVideos
CREATE PROCEDURE GetLatestVideos
    @Limit INT
AS
BEGIN
    SELECT TOP (@Limit) *
    FROM Videos
    WHERE is_inactive = 0
    ORDER BY video_id DESC;
END


--------------edit video.................
CREATE PROCEDURE EditVideo
    @video_id INT,
    @title NVARCHAR(255) = NULL,
    @price DECIMAL(10, 2) = NULL,
    @description NVARCHAR(MAX) = NULL,
    @type NVARCHAR(50) = NULL,
    @episodes INT = NULL,
    @duration TIME = NULL,
    @is_video BIT = NULL,
    @download_url NVARCHAR(255) = NULL,
    @image_url NVARCHAR(255) = NULL,
	@release_day DATE = NULL,
    @actor_ids NVARCHAR(MAX) = NULL,
    @category_ids NVARCHAR(MAX) = NULL,
    @is_inactive BIT = NULL
AS
BEGIN
    UPDATE Videos
    SET 
        title = COALESCE(@title, title),
        price = COALESCE(@price, price),
        description = COALESCE(@description, description),
        type = COALESCE(@type, type),
        episodes = COALESCE(@episodes, episodes),
        duration = COALESCE(@duration, duration),
        is_video = COALESCE(@is_video, is_video),
        download_url = COALESCE(@download_url, download_url),
        image_url = COALESCE(@image_url, image_url),
		release_day = COALESCE(@release_day, release_day),
        is_inactive = COALESCE(@is_inactive, is_inactive)
    WHERE video_id = @video_id;

    IF @actor_ids IS NOT NULL
    BEGIN

        DELETE va FROM VideoActors va
        WHERE va.video_id = @video_id
          AND NOT EXISTS (SELECT 1 FROM STRING_SPLIT(@actor_ids, ',') s WHERE va.actor_id = s.value);


        INSERT INTO VideoActors (video_id, actor_id)
        SELECT @video_id, value
        FROM STRING_SPLIT(@actor_ids, ',') s
        WHERE NOT EXISTS (SELECT 1 FROM VideoActors va2 WHERE va2.video_id = @video_id AND va2.actor_id = s.value);
    END


    IF @category_ids IS NOT NULL
    BEGIN

        DELETE vc FROM VideoCategories vc
        WHERE vc.video_id = @video_id
          AND NOT EXISTS (SELECT 1 FROM STRING_SPLIT(@category_ids, ',') s WHERE vc.category_id = s.value);


        INSERT INTO VideoCategories (video_id, category_id)
        SELECT @video_id, value
        FROM STRING_SPLIT(@category_ids, ',') s
        WHERE NOT EXISTS (SELECT 1 FROM VideoCategories vc2 WHERE vc2.video_id = @video_id AND vc2.category_id = s.value);
    END
END;





-----------------disable video
CREATE PROCEDURE InactiveVideo
    @video_id INT
AS
BEGIN
    UPDATE Videos
    SET is_inactive = 1
    WHERE video_id = @video_id;
END

-----------------Enable video
CREATE PROCEDURE ActiveVideo
    @video_id INT
AS
BEGIN
    UPDATE Videos
    SET is_inactive = 0
    WHERE video_id = @video_id;
END


-----------------delete video
CREATE PROCEDURE DeleteVideo
    @video_id INT
AS
BEGIN
    DELETE FROM VideoActors
    WHERE video_id = @video_id;

    DELETE FROM VideoCategories
    WHERE video_id = @video_id;

    DELETE FROM Videos
    WHERE video_id = @video_id;

	DELETE FROM Ratings
    WHERE video_id = @video_id;
END

---------------------Show all video
CREATE PROCEDURE GetVideos
    @searchQuery NVARCHAR(255) = '',
    @categoryId INT = NULL
AS
BEGIN
    SET NOCOUNT ON;

    SELECT v.*
    FROM Videos v
    WHERE v.is_inactive = 0
      AND (@categoryId IS NULL OR EXISTS (
          SELECT 1
          FROM VideoCategories vc
          WHERE vc.video_id = v.video_id
            AND vc.category_id = @categoryId
      ))
      AND v.title LIKE '%' + @searchQuery + '%'
    ORDER BY v.title;
END




-----------------------------------------Actor---------------------------
---GetAllActors
CREATE PROCEDURE GetAllActors
AS
BEGIN
    SELECT actor_id, actor_name
    FROM Actors;
END;

---AddActor
CREATE PROCEDURE AddActor
    @actorName NVARCHAR(100)
AS
BEGIN
    IF EXISTS (SELECT 1 FROM Actors WHERE actor_name = @actorName)
    BEGIN
        RAISERROR('Already in actors list!', 16, 1);
        RETURN;
    END

    INSERT INTO Actors (actor_name) VALUES (@actorName);
END;


---------------UpdateActor
CREATE PROCEDURE UpdateActor
    @actorId INT,
    @newActorName NVARCHAR(100)
AS
BEGIN
    IF NOT EXISTS (SELECT 1 FROM Actors WHERE actor_id = @actorId)
    BEGIN
        RAISERROR('Already in actors list!', 16, 1);
        RETURN;
    END

    UPDATE Actors
    SET actor_name = @newActorName
    WHERE actor_id = @actorId;
END;





-----------------DeleteActor
CREATE PROCEDURE DeleteActors
    @actorId INT
AS
BEGIN
    IF NOT EXISTS (SELECT 1 FROM Actors WHERE actor_id = @actorId)
    BEGIN
        RAISERROR('Actors not found!', 16, 1);
        RETURN;
    END

    DELETE FROM VideoActors WHERE actor_id = @actorId;

    DELETE FROM Actors WHERE actor_id = @actorId;
END;


-----------------------------------Comment and rating
-----User add cmt and rating
CREATE PROCEDURE AddCommentAndRating
    @videoId INT,
    @userId INT,
    @rating INT,
    @comment TEXT = NULL 
AS
BEGIN
    INSERT INTO Ratings (video_id, user_id, rating, comment)
    VALUES (@videoId, @userId, @rating, @comment);
END;


----------UpdateCommentAndRating
CREATE PROCEDURE UpdateCommentAndRating
    @ratingId INT,
    @newRating INT = NULL,
    @newComment TEXT = NULL
AS
BEGIN
    UPDATE Ratings
    SET rating = COALESCE(@newRating, rating),
        comment = COALESCE(@newComment, comment)
    WHERE rating_id = @ratingId;
END;

----------DeleteCommentAndRating
CREATE PROCEDURE DeleteCommentAndRating
    @ratingId INT
AS
BEGIN
    DELETE FROM Ratings WHERE rating_id = @ratingId;
END;


----------
CREATE PROCEDURE GetAllCommentsAndRatings
    @videoId INT
AS
BEGIN
    SELECT r.rating_id, r.user_id, u.username, r.rating, r.comment
    FROM Ratings r
    JOIN Users u ON r.user_id = u.user_id
    WHERE r.video_id = @videoId;
END;




----------------------------------------------------------------------------Function----------------------

--SearchVideos
CREATE FUNCTION dbo.SearchVideos (@searchQuery NVARCHAR(100))
RETURNS TABLE
AS
RETURN
(
    SELECT *
    FROM Videos
    WHERE title LIKE '%' + @searchQuery + '%'
);
	




------get all  video
CREATE FUNCTION GetAllVideos()
RETURNS TABLE
AS
RETURN
(
    SELECT 
        video_id,
        title,
        price,
        description,
        type,
        episodes,
        duration,
        is_video,
        download_url,
        image_url,
        is_inactive
    FROM Videos
);


SELECT * FROM dbo.GetAllVideos()

----Get Video Detail
CREATE FUNCTION GetVideoDetails(@videoId INT)
RETURNS TABLE
AS
RETURN
(
    SELECT 
        v.video_id, 
        v.title, 
        v.price, 
        v.description, 
        v.image_url, 
        v.type, 
        v.episodes, 
        v.duration, 
        v.is_video,
        a.actor_name,
        c.category_name,
		v.download_url,
		v.release_day
    FROM Videos v
    LEFT JOIN VideoActors va ON v.video_id = va.video_id
    LEFT JOIN Actors a ON va.actor_id = a.actor_id
    LEFT JOIN VideoCategories vc ON v.video_id = vc.video_id
    LEFT JOIN Categories c ON vc.category_id = c.category_id
    WHERE v.video_id = @videoId
);

-- GetOrderDetails TVF
CREATE FUNCTION GetOrderDetails(@orderId INT)
RETURNS TABLE
AS
RETURN
(
    SELECT o.order_id, u.username, o.order_date, o.total_amount
    FROM Orders o
    JOIN Users u ON o.user_id = u.user_id
    WHERE o.order_id = @orderId
);

-- GetOrderItems TVF
CREATE FUNCTION GetOrderItems(@orderId INT)
RETURNS TABLE
AS
RETURN
(
    SELECT v.title, oi.price
    FROM OrderItems oi
    JOIN Videos v ON oi.video_id = v.video_id
    WHERE oi.order_id = @orderId
);




---------------------------------------------------------------------------------Views
--All Orders (for Admin):
CREATE VIEW AllOrders AS
SELECT o.order_id, u.username, o.order_date, o.total_amount
FROM Orders o
JOIN Users u ON o.user_id = u.user_id;

--------- ViewPurchaseHistory
CREATE VIEW UserPurchaseHistory AS
SELECT 
    u.user_id, 
    u.username, 
    o.order_id, 
    o.order_date, 
    o.total_amount, 
    oi.price,
    v.title AS video_title, 
    v.video_id AS video_id,
    p.payment_date, 
    p.payment_method, 
    p.payment_status
FROM 
    Users u
JOIN 
    Orders o ON u.user_id = o.user_id
JOIN 
    OrderItems oi ON o.order_id = oi.order_id
JOIN 
    Videos v ON oi.video_id = v.video_id
JOIN 
    PaymentHistory p ON o.order_id = p.order_id;

-- UserPurchasedVideos
CREATE VIEW UserPurchasedVideos AS
SELECT 
    u.user_id, 
    u.username, 
    v.video_id, 
    v.title, 
    v.image_url,
    o.order_date,
	v.download_url
FROM Users u
JOIN Orders o ON u.user_id = o.user_id
JOIN OrderItems oi ON o.order_id = oi.order_id
JOIN Videos v ON oi.video_id = v.video_id;

-------------------- AllCommentsAndRatings (view)
CREATE VIEW AllCommentsAndRatings AS
SELECT 
    r.rating_id, 
    v.title AS video_title, 
    u.username, 
    r.rating, 
    r.comment, 
    r.video_id 
FROM Ratings r
JOIN Users u ON r.user_id = u.user_id
JOIN Videos v ON r.video_id = v.video_id;






--------------------Trigger
CREATE TRIGGER ApplyBulkDiscount
ON OrderItems
AFTER INSERT
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @orderId INT;
    DECLARE @userId INT;
    DECLARE @totalAmount DECIMAL(10,2);

    SELECT @orderId = order_id FROM inserted;
    SELECT @userId = user_id FROM Orders WHERE order_id = @orderId;

    IF (SELECT COUNT(*) FROM OrderItems WHERE order_id = @orderId) >= 3
    BEGIN
        SELECT @totalAmount = SUM(price) FROM OrderItems WHERE order_id = @orderId;
        SET @totalAmount = @totalAmount * 0.8; 

        UPDATE Orders SET total_amount = @totalAmount WHERE order_id = @orderId;
    END
END;