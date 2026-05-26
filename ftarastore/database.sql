-- ============================================================
-- ftarastore - Database Schema
-- Engine: MariaDB (InfinityFree Compatible)
-- ============================================================

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE users (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name                VARCHAR(100)  NOT NULL,
    email               VARCHAR(150)  NOT NULL UNIQUE,
    phone               VARCHAR(20)   NULL,
    password            VARCHAR(255)  NOT NULL,
    role                ENUM('user','admin','super_admin') NOT NULL DEFAULT 'user',
    is_active           TINYINT(1)    NOT NULL DEFAULT 1,
    is_verified         TINYINT(1)    NOT NULL DEFAULT 0,
    email_verified_at   TIMESTAMP     NULL,
    email_verify_token  VARCHAR(64)   NULL,
    two_fa_secret       VARCHAR(64)   NULL COMMENT 'TOTP secret key',
    two_fa_enabled      TINYINT(1)    NOT NULL DEFAULT 0,
    two_fa_backup_codes TEXT          NULL COMMENT 'JSON backup codes',
    password_changed_at TIMESTAMP     NULL,
    last_login_at       TIMESTAMP     NULL,
    last_login_ip       VARCHAR(45)   NULL,
    failed_login_count  INT           NOT NULL DEFAULT 0,
    locked_until        TIMESTAMP     NULL COMMENT 'Account lockout',
    created_at          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: rate_limits
-- ============================================================
CREATE TABLE rate_limits (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key`      VARCHAR(255)  NOT NULL UNIQUE,
    attempts   INT           NOT NULL DEFAULT 0,
    expires_at TIMESTAMP     NOT NULL,
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: audit_logs
-- ============================================================
CREATE TABLE audit_logs (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NULL,
    action      VARCHAR(80)   NOT NULL,
    description TEXT          NULL,
    ip_address  VARCHAR(45)   NULL,
    user_agent  VARCHAR(255)  NULL,
    extra       LONGTEXT      NULL COMMENT 'JSON data',
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_ip (ip_address),
    INDEX idx_created (created_at),
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: ip_blacklist
-- ============================================================
CREATE TABLE ip_blacklist (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip         VARCHAR(45)   NOT NULL UNIQUE,
    reason     VARCHAR(255)  NULL,
    blocked_by BIGINT UNSIGNED NULL,
    expires_at TIMESTAMP     NULL COMMENT 'NULL = permanent',
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip (ip),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: user_sessions
-- ============================================================
CREATE TABLE user_sessions (
    id            VARCHAR(128)    NOT NULL PRIMARY KEY,
    user_id       BIGINT UNSIGNED NOT NULL,
    ip_address    VARCHAR(45)     NULL,
    user_agent    VARCHAR(255)    NULL,
    last_activity TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    CONSTRAINT fk_sess_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: password_resets
-- ============================================================
CREATE TABLE password_resets (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(150)  NOT NULL,
    token      VARCHAR(64)   NOT NULL UNIQUE,
    expires_at TIMESTAMP     NOT NULL,
    used_at    TIMESTAMP     NULL,
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: categories
-- ============================================================
CREATE TABLE categories (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(80)  NOT NULL,
    slug       VARCHAR(80)  NOT NULL UNIQUE,
    icon       VARCHAR(10)  NULL COMMENT 'Emoji icon',
    sort_order INT          NOT NULL DEFAULT 0,
    is_active  TINYINT(1)   NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: games
-- ============================================================
CREATE TABLE games (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id   INT UNSIGNED    NOT NULL,
    name          VARCHAR(120)    NOT NULL,
    slug          VARCHAR(120)    NOT NULL UNIQUE,
    publisher     VARCHAR(100)    NULL,
    image_url     VARCHAR(255)    NULL,
    banner_url    VARCHAR(255)    NULL,
    description   TEXT            NULL,
    has_server_id TINYINT(1)      NOT NULL DEFAULT 0 COMMENT 'Apakah butuh Server/Zone ID',
    is_popular    TINYINT(1)      NOT NULL DEFAULT 0,
    sort_order    INT             NOT NULL DEFAULT 0,
    is_active     TINYINT(1)      NOT NULL DEFAULT 1,
    created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_games_category FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: products
-- ============================================================
CREATE TABLE products (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_id      BIGINT UNSIGNED NOT NULL,
    name         VARCHAR(150)    NOT NULL,
    digi_code    VARCHAR(100)    NOT NULL COMMENT 'Kode produk Digiflazz',
    digi_brand   VARCHAR(100)    NULL     COMMENT 'Brand/provider Digiflazz',
    description  VARCHAR(255)    NULL,
    price_modal  DECIMAL(12,2)   NOT NULL DEFAULT 0,
    price_sell   DECIMAL(12,2)   NOT NULL DEFAULT 0,
    is_active    TINYINT(1)      NOT NULL DEFAULT 1,
    sort_order   INT             NOT NULL DEFAULT 0,
    created_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_game FOREIGN KEY (game_id) REFERENCES games(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: orders
-- ============================================================
CREATE TABLE orders (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_code   VARCHAR(30)     NOT NULL UNIQUE COMMENT 'Kode unik order, ex: FTS-20240101-XXXX',
    user_id      BIGINT UNSIGNED NULL COMMENT 'NULL = guest',
    product_id   BIGINT UNSIGNED NOT NULL,
    game_user_id VARCHAR(100)    NOT NULL COMMENT 'ID akun game user',
    server_id    VARCHAR(50)     NULL     COMMENT 'Server/Zone ID (jika ada)',
    buyer_email  VARCHAR(150)    NOT NULL,
    buyer_phone  VARCHAR(20)     NULL,
    product_name VARCHAR(150)    NOT NULL COMMENT 'Snapshot nama produk saat order',
    amount       DECIMAL(12,2)   NOT NULL COMMENT 'Harga jual saat order',
    status       ENUM('pending','paid','processing','success','failed','refunded') NOT NULL DEFAULT 'pending',
    notes        TEXT            NULL,
    created_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE SET NULL,
    CONSTRAINT fk_orders_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: payments
-- ============================================================
CREATE TABLE payments (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id       BIGINT UNSIGNED NOT NULL UNIQUE,
    midtrans_id    VARCHAR(100)    NULL COMMENT 'Transaction ID dari Midtrans',
    snap_token     VARCHAR(255)    NULL COMMENT 'Snap token Midtrans',
    payment_method VARCHAR(60)     NULL COMMENT 'gopay/bca_va/qris/dll',
    gross_amount   DECIMAL(12,2)   NOT NULL,
    status         ENUM('pending','settlement','capture','cancel','deny','expire','refund') NOT NULL DEFAULT 'pending',
    midtrans_status VARCHAR(50)    NULL COMMENT 'Raw status dari Midtrans',
    paid_at        TIMESTAMP       NULL,
    expired_at     TIMESTAMP       NULL,
    raw_response   TEXT            NULL COMMENT 'JSON response Midtrans',
    created_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: transactions (Digiflazz log)
-- ============================================================
CREATE TABLE transactions (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id       BIGINT UNSIGNED NOT NULL UNIQUE,
    digi_ref_id    VARCHAR(100)    NULL COMMENT 'ref_id / trx_id dari Digiflazz',
    digi_buyer_sku VARCHAR(100)    NULL,
    status         ENUM('pending','process','sukses','gagal') NOT NULL DEFAULT 'pending',
    message        VARCHAR(255)    NULL COMMENT 'Pesan dari Digiflazz',
    sn             VARCHAR(255)    NULL COMMENT 'Serial Number jika produk voucher',
    price          DECIMAL(12,2)   NULL COMMENT 'Harga modal dari Digiflazz',
    raw_response   TEXT            NULL COMMENT 'JSON response Digiflazz',
    created_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_transactions_order FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: banners
-- ============================================================
CREATE TABLE banners (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(150)  NULL,
    image_url  VARCHAR(255)  NOT NULL,
    link_url   VARCHAR(255)  NULL,
    sort_order INT           NOT NULL DEFAULT 0,
    is_active  TINYINT(1)    NOT NULL DEFAULT 1,
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: settings
-- ============================================================
CREATE TABLE settings (
    `key`      VARCHAR(80)  NOT NULL PRIMARY KEY,
    `value`    TEXT         NULL,
    updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: vouchers
-- ============================================================
CREATE TABLE vouchers (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code         VARCHAR(32)     NOT NULL UNIQUE,
    type         ENUM('percent','fixed') NOT NULL DEFAULT 'fixed',
    value        DECIMAL(12,2)   NOT NULL DEFAULT 0,
    min_purchase DECIMAL(12,2)   NOT NULL DEFAULT 0,
    max_discount DECIMAL(12,2)   NULL COMMENT 'Maks diskon untuk tipe persen',
    quota        INT             NOT NULL DEFAULT 1,
    used_count   INT             NOT NULL DEFAULT 0,
    is_active    TINYINT(1)      NOT NULL DEFAULT 1,
    description  VARCHAR(255)    NULL,
    expires_at   DATETIME        NULL,
    created_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: digi_products (katalog hasil Sync DigiFlazz)
-- ============================================================
CREATE TABLE digi_products (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sku_code     VARCHAR(100)  NOT NULL UNIQUE COMMENT 'buyer_sku_code dari DigiFlazz',
    product_name VARCHAR(250)  NOT NULL,
    brand        VARCHAR(100)  NULL COMMENT 'Brand/provider, dipakai untuk match ke game',
    category     VARCHAR(80)   NOT NULL DEFAULT 'game',
    price        INT UNSIGNED  NOT NULL DEFAULT 0 COMMENT 'Harga modal dari DigiFlazz',
    price_sell   INT UNSIGNED  NOT NULL DEFAULT 0 COMMENT 'Harga jual = modal + markup',
    description  TEXT          NULL,
    is_active    TINYINT(1)    NOT NULL DEFAULT 1,
    created_at   TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_digi_brand    (brand),
    INDEX idx_digi_category (category),
    INDEX idx_digi_active   (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: promo_events (Event Game di halaman Promo)
-- ============================================================
CREATE TABLE promo_events (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    emoji       VARCHAR(10)   DEFAULT '🎮',
    color       VARCHAR(40)   DEFAULT 'rgba(56,189,248,.1)',
    game        VARCHAR(100)  NULL,
    title       VARCHAR(150)  NOT NULL,
    description VARCHAR(255)  NULL,
    period      VARCHAR(100)  NULL,
    link_url    VARCHAR(300)  NULL,
    status      ENUM('live','upcoming','ended') NOT NULL DEFAULT 'live',
    sort_order  SMALLINT      NOT NULL DEFAULT 0,
    is_active   TINYINT(1)    NOT NULL DEFAULT 1,
    created_at  TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: explore_streams (Live Streaming di halaman Explore)
-- ============================================================
CREATE TABLE explore_streams (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(180)  NOT NULL,
    streamer    VARCHAR(100)  NULL,
    game        VARCHAR(100)  NULL,
    platform    ENUM('youtube','twitch','facebook','tiktok','other') NOT NULL DEFAULT 'youtube',
    url         VARCHAR(300)  NOT NULL,
    emoji       VARCHAR(10)   DEFAULT '📺',
    viewers     VARCHAR(40)   NULL,
    status      ENUM('live','upcoming','ended') NOT NULL DEFAULT 'live',
    sort_order  SMALLINT      NOT NULL DEFAULT 0,
    is_active   TINYINT(1)    NOT NULL DEFAULT 1,
    created_at  TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: explore_categories (kartu kategori di halaman Explore)
-- ============================================================
CREATE TABLE explore_categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cat_key     VARCHAR(40)   NOT NULL UNIQUE,
    label       VARCHAR(60)   NOT NULL,
    emoji       VARCHAR(10)   DEFAULT '📁',
    color       VARCHAR(40)   DEFAULT 'rgba(227,24,55,.6)',
    bg          VARCHAR(40)   DEFAULT 'rgba(227,24,55,.1)',
    badge       VARCHAR(40)   DEFAULT 'var(--red)',
    tag         VARCHAR(30)   NULL,
    description VARCHAR(150)  NULL,
    sort_order  SMALLINT      NOT NULL DEFAULT 0,
    is_active   TINYINT(1)    NOT NULL DEFAULT 1,
    created_at  TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- INDEXES
-- ============================================================
CREATE INDEX idx_orders_code    ON orders(order_code);
CREATE INDEX idx_orders_email   ON orders(buyer_email);
CREATE INDEX idx_orders_status  ON orders(status);
CREATE INDEX idx_products_game  ON products(game_id);
CREATE INDEX idx_games_slug     ON games(slug);
CREATE INDEX idx_games_popular  ON games(is_popular);

-- ============================================================
-- SEED DATA
-- ============================================================

-- Categories
INSERT INTO categories (name, slug, icon, sort_order) VALUES
('Top Up Games',  'topup-games',   '🎮', 1),
('Pulsa & Data',  'pulsa-data',    '📱', 2),
('Voucher',       'voucher',       '🎁', 3),
('Entertainment', 'entertainment', '🎬', 4),
('Tagihan',       'tagihan',       '💡', 5);

-- Admin & Super Admin
INSERT INTO users (name, email, phone, password, role, is_active, is_verified, email_verified_at) VALUES
('Super Admin',      'superadmin@ftarastore.com', '08100000000', '$2y$12$placeholder_run_setup_php', 'super_admin', 1, 1, NOW()),
('Admin ftarastore', 'admin@ftarastore.com',      '08123456789', '$2y$12$placeholder_run_setup_php', 'admin',       1, 1, NOW())
ON DUPLICATE KEY UPDATE role=VALUES(role), is_active=1, is_verified=1;

-- Default settings
INSERT INTO settings (`key`, `value`) VALUES
('site_name',       'ftarastore'),
('site_tagline',    'Top Up Game Murah, Cepat & Terpercaya'),
('whatsapp_number', '628123456789'),
('instagram_url',   'https://instagram.com/ftarastore'),
('digiflazz_url',   'https://api.digiflazz.com/v1'),
('midtrans_env',    'sandbox'),
('fee_type',        'percent'),
('fee_value',       '5');

-- Sample games
INSERT INTO games (category_id, name, slug, publisher, has_server_id, is_popular, sort_order) VALUES
(1, 'Mobile Legends',       'mobile-legends',      'Moonton',       1, 1, 1),
(1, 'Free Fire',            'free-fire',           'Garena',        0, 1, 2),
(1, 'PUBG Mobile',          'pubg-mobile',         'Tencent Games', 0, 1, 3),
(1, 'Genshin Impact',       'genshin-impact',      'miHoYo',        1, 1, 4),
(1, 'Valorant',             'valorant',            'Riot Games',    0, 1, 5),
(1, 'Call of Duty Mobile',  'call-of-duty-mobile', 'Activision',    0, 1, 6),
(1, 'MLBB Magic Chess',     'magic-chess',         'Moonton',       1, 0, 7),
(1, 'Roblox',               'roblox',              'Roblox Corp',   0, 1, 8);

-- Sample banners
INSERT INTO banners (title, image_url, link_url, sort_order) VALUES
('Banner Mobile Legends', '/assets/images/banner1.jpg', '/game/mobile-legends', 1),
('Banner Free Fire',      '/assets/images/banner2.jpg', '/game/free-fire',      2),
('Banner PUBG',           '/assets/images/banner3.jpg', '/game/pubg-mobile',    3);