# 0. 초기 설정
DROP DATABASE IF EXISTS steamdb;
DROP USER IF EXISTS '202101472user'@'localhost';
CREATE USER '202101472user'@'localhost' IDENTIFIED WITH mysql_native_password BY '202101472pw';
CREATE DATABASE steamdb;
GRANT ALL PRIVILEGES ON steamdb.* TO '202101472user'@'localhost' WITH GRANT OPTION;
COMMIT;
USE steamdb;

# Game Table
CREATE TABLE Game (
    game_id INTEGER PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    genre VARCHAR(100),
    developer VARCHAR(100),
    price INTEGER,
    rating ENUM(
        'Overwhelmingly Positive',
        'Very Positive',
        'Positive',
        'Mostly Positive',
        'Mixed',
        'Mostly Negative',
        'Negative',
        'Very Negative',
        'Overwhelmingly Negative'
    ) NOT NULL
);

# Customer Table
CREATE TABLE Customer (
    customer_id INTEGER PRIMARY KEY,
    nickname VARCHAR(50),
    bank DECIMAL(10, 2),
    language VARCHAR(20),
    email VARCHAR(100)
);

# Sale Table
CREATE TABLE Sale (
    sale_id INTEGER PRIMARY KEY,
    game_id INTEGER,
    discount_rate FLOAT,
    discount_period VARCHAR(100),
    reason TEXT,
    FOREIGN KEY (game_id) REFERENCES Game(game_id)
);

# OrderList Table
CREATE TABLE OrderList (
    order_id INTEGER PRIMARY KEY,
    customer_id INTEGER,
    game_id INTEGER,
    sale_id INTEGER,
    payed_price FLOAT,
    order_time DATETIME,
    payment_method VARCHAR(50),
    FOREIGN KEY (customer_id) REFERENCES Customer(customer_id),
    FOREIGN KEY (game_id) REFERENCES Game(game_id),
    FOREIGN KEY (sale_id) REFERENCES Sale(sale_id)
);

# PlayList Table
CREATE TABLE PlayList (
    customer_id INTEGER,
    game_id INTEGER,
    play_time FLOAT(7,1),
    PRIMARY KEY (customer_id, game_id),
    FOREIGN KEY (customer_id) REFERENCES Customer(customer_id),
    FOREIGN KEY (game_id) REFERENCES Game(game_id)
);

#로그인을 위한 테이블 수정단계
ALTER TABLE Customer ADD COLUMN password VARCHAR(50) NOT NULL; #패스워드 추가
ALTER TABLE Customer ADD COLUMN is_admin TINYINT(1) DEFAULT 0; #관리자 판별기

#OrderList가 수정되거나 삭제되는 경우를 위해 추가
ALTER TABLE OrderList ADD COLUMN discount_rate FLOAT;


#insert place
# Game에 데이터를 넣는 인설트문
INSERT INTO Game (game_id, name, genre, developer, price, rating) VALUES
(1001, 'Mount & Blade: Warband', 'Action, RPG', 'TaleWorlds Entertainment', 24000, 'Overwhelmingly Positive'),
(1002, 'Mount & Blade II: Bannerlord', 'Action, RPG, Strategy', 'TaleWorlds Entertainment', 60000, 'Very Positive'),
(2001, 'Sid Meier''s Civilization VI', 'Turn-Based Strategy, 4X', 'Firaxis Games', 65000, 'Very Positive'),
(2002, 'Total War: MEDIEVAL II - Definitive Edition', 'Real-Time Tactics, Turn-Based Strategy', 'Creative Assembly', 25000, 'Overwhelmingly Positive'),
(2003, 'Total War: THREE KINGDOMS', 'Turn-Based Strategy, Real-Time Tactics', 'Creative Assembly', 59800, 'Very Positive'),
(2004, 'Total War: ATTILA', 'Turn-Based Strategy, Real-Time Tactics', 'Creative Assembly', 45000, 'Mostly Positive'),
(2005, 'Total War: SHOGUN 2', 'Turn-Based Strategy, Real-Time Tactics', 'Creative Assembly', 24000, 'Overwhelmingly Positive'),
(2006, 'Total War: ROME II - Emperor Edition', 'Turn-Based Strategy, Real-Time Tactics', 'Creative Assembly', 58000, 'Mixed'),
(3001, '7 Days to Die', 'Survival, Horror, Open World', 'The Fun Pimps', 47000, 'Very Positive'),
(4001, 'Balatro', 'Roguelike, Card Game, Strategy', 'LocalThunk', 16500, 'Overwhelmingly Positive'),
(9999, 'Deleted Game', 'Unknown', 'Unknown', 0, 'Mixed');

# Customer에 데이터를 넣는 인설트문
INSERT INTO Customer (customer_id, nickname, bank, language, email, password, is_admin) VALUES
(1, 'HONGGILDONG', 50000.00, 'Korean', 'hong@steam.com', 'HONGGILDONG', 0),
(2, 'IMKKEOKJEONG', 32000.50, 'English', 'imkk@steam.com', 'IMKKEOKJEONG', 0),
(3, 'BYEOLJUBU', 10000.00, 'Japanese', 'byeol@steam.com', 'BYEOLJUBU', 0),
(4, 'JEONUCHI', 88000.75, 'Korean', 'jeon@steam.com', 'JEONUCHI', 0),
(5, 'GEOBUGI', 7500.00, 'English', 'geobugi@steam.com', 'GEOBUGI', 0),
(6, 'GIMHYEONGHO', 15000.00, 'Korean', 'hyoungho@steam.com', 'GIMHYEONGHO', 0),
(7, 't', 50000.00, 'Korean', 't@steam.com', 't', 0),
(999, 'admin', 0, 'Korean', 'admin@steam.com', 'admin', 1);


# Sale에 데이터를 넣는 인설트문
INSERT INTO Sale (sale_id, game_id, discount_rate, discount_period, reason) VALUES
(1, 1001, 0.00, '0000-01-01 ~ 9999-01-01', 'null'),
(2, 1002, 0.30, '2025-04-10 ~ 2025-04-20', 'forLAB'),
(3, 2002, 0.20, '2025-03-01 ~ 2025-06-15', 'forLAB'),
(4, 2002, 0.75, '2025-05-01 ~ 2025-05-06', 'wargameFestival'),
(5, 2003, 0.75, '2025-05-01 ~ 2025-05-06', 'wargameFestival'),
(6, 2004, 0.75, '2025-05-01 ~ 2025-05-06', 'wargameFestival'),
(7, 2005, 0.75, '2025-05-01 ~ 2025-05-06', 'wargameFestival'),
(8, 2006, 0.75, '2025-05-01 ~ 2025-05-06', 'wargameFestival'),
(9, 3001, 0.40, '2025-05-01 ~ 2025-05-07', 'forLAB2'),
(10, 4001, 0.15, '2025-05-01 ~ 2025-05-07', 'forLAB2'),
(11, 2001, 0.05, '2025-03-01 ~ 2025-06-30', 'forLABMakeerr'),
(12, 2002, 0.30, '2025-03-01 ~ 2025-06-30', 'forLABMakeerr');

# PlayList에 데이터를 넣는 인설트문
INSERT INTO PlayList (customer_id, game_id, play_time) VALUES
(1, 1001, 1200),
(1, 1002, 300),
(2, 2001, 800),
(3, 2002, 600),
(5, 4001, 90),
(6, 3001, 98.8),
(7, 3001, 98.8),
(7, 4001, 93.8);
# orderList에 데이터를 넣는 인설트문
INSERT INTO OrderList (order_id, customer_id, game_id, sale_id, payed_price, order_time, payment_method,discount_rate) VALUES
(3, 3, 2001, 1, 65000, '2025-03-03 09:55:00', 'KakaoPay',0.00),
(1, 1, 1001, 1, 24000, '2025-04-02 14:33:00', 'Credit Card',0.00),
(2, 1, 1002, 2, 42000, '2025-04-11 11:12:00', 'Paypal',0.30),
(4, 6, 4001, 1, 16500, '2025-04-27 12:45:00', 'NaverPay',0.75),
(5, 3, 2002, 3, 20000, '2025-04-28 18:30:00', 'Credit Card',0.20),
(6, 5, 3001, 9, 28200, '2025-05-02 20:00:00', 'Toss',0.40),
(7, 7, 3001, 9, 28200, '2025-05-02 20:00:00', 'Toss',0.40),
(8, 7, 4001, 10, 11550, '2025-05-02 20:00:00', 'Toss',0.15);

#select place
#유저가 평가가 매우 긍정적인 게임(Very Positive)만 노출을 선택했을 때 사용할 셀렉트문
SELECT * FROM Game WHERE rating = 'Very Positive';

#MOSTPLAY TOP3, 인기 top3 게임을 광고할 때 사용할 셀렉트문
SELECT G.name, P.play_time
FROM PlayList P
JOIN Game G ON P.game_id = G.game_id
WHERE P.customer_id = 1
ORDER BY P.play_time DESC
LIMIT 3;

#유저가 주문내역을 확인할 때 사용될 코드
SELECT G.name, O.payment_method, O.order_time
FROM OrderList O
JOIN Game G ON O.game_id = G.game_id
WHERE O.customer_id = 2;

#update place
#유저가 게임을 더 플레이해 플레이타이밍 늘어난 경우 사용할 업데이트문
UPDATE PlayList
SET play_time = 102.5
WHERE customer_id = 6 AND game_id = 3001;

#유저가 지갑에 돈을 충전한 경우
UPDATE Customer
SET bank = bank + 50000
WHERE customer_id=1;


# 주문 시 기간 고려, 중복 할인 시 가장 높은 1개 적용
SET @game_id := 2002;
# 현재 시각을 기준으로 할인 중인 가장 높은 할인율의 sale_id 가져오기
SET @sale_id := (
  SELECT sale_id FROM Sale
  WHERE game_id = @game_id
    AND NOW() BETWEEN
      STR_TO_DATE(SUBSTRING_INDEX(discount_period, ' ~ ', 1), '%Y-%m-%d')
      AND
      STR_TO_DATE(SUBSTRING_INDEX(discount_period, ' ~ ', -1), '%Y-%m-%d')
  ORDER BY discount_rate DESC
  LIMIT 1
);
# 3. 할인 없으면 기본값 1로 대체
SET @sale_id := IFNULL(@sale_id, 1);

# 할인율 가져오기 및 결제 금액 계산
SET @discount := (SELECT discount_rate FROM Sale WHERE sale_id = @sale_id);
SET @price := (SELECT price FROM Game WHERE game_id = @game_id);
SET @payed_price := @price * (1 - @discount);
#orderList에 insert하는 인설트문
INSERT INTO OrderList (order_id, customer_id, game_id, sale_id, payed_price, order_time, payment_method,discount_rate)
VALUES (9, 1, @game_id, @sale_id, @payed_price, NOW(), 'Toss', @discount);

#orderList에 insert하고, 커스터머의 bank값을 업데이트
UPDATE Customer
SET bank = (bank - @payed_price)
WHERE customer_id = 1;


SELECT * FROM sale





COMMIT;
