-- ============================================================
-- MIGRATION: Panel Admin Promo, Reward & Live Streaming Explore
-- Jalankan sekali di phpMyAdmin / mysql CLI pada database ftarastore.
-- ============================================================

-- ── Event Game (ditampilkan di pages/promo.php) ──
CREATE TABLE IF NOT EXISTS promo_events (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  emoji       VARCHAR(10)   DEFAULT '🎮',
  color       VARCHAR(40)   DEFAULT 'rgba(56,189,248,.1)',
  game        VARCHAR(100)  DEFAULT NULL,
  title       VARCHAR(150)  NOT NULL,
  description VARCHAR(255)  DEFAULT NULL,
  period      VARCHAR(100)  DEFAULT NULL,
  link_url    VARCHAR(300)  DEFAULT NULL,
  status      ENUM('live','upcoming','ended') NOT NULL DEFAULT 'live',
  sort_order  SMALLINT      NOT NULL DEFAULT 0,
  is_active   TINYINT(1)    NOT NULL DEFAULT 1,
  created_at  TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed contoh (boleh dihapus)
INSERT INTO promo_events (emoji,color,game,title,description,period,status,sort_order) VALUES
('⚔️','rgba(56,189,248,.1)','Mobile Legends','Double Diamond Event','Beli diamond ML selama weekend, dapat bonus 10% extra diamond!','Setiap Sabtu-Minggu','live',1),
('🔥','rgba(251,146,60,.1)','Free Fire','Top Up Hari Kemerdekaan','Spesial HUT RI, top up FF dapat bonus diamond dan skin eksklusif.','17–31 Agustus','upcoming',2),
('💎','rgba(167,139,250,.1)','Genshin Impact','Blessing of the Welkin Moon','Dapatkan harga terbaik untuk top up Genesis Crystal.','Berlaku terus','live',3);

-- ── Live Streaming (ditampilkan di pages/explore.php) ──
CREATE TABLE IF NOT EXISTS explore_streams (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(180)  NOT NULL,
  streamer    VARCHAR(100)  DEFAULT NULL,
  game        VARCHAR(100)  DEFAULT NULL,
  platform    ENUM('youtube','twitch','facebook','tiktok','other') NOT NULL DEFAULT 'youtube',
  url         VARCHAR(300)  NOT NULL,
  emoji       VARCHAR(10)   DEFAULT '📺',
  viewers     VARCHAR(40)   DEFAULT NULL,
  status      ENUM('live','upcoming','ended') NOT NULL DEFAULT 'live',
  sort_order  SMALLINT      NOT NULL DEFAULT 0,
  is_active   TINYINT(1)    NOT NULL DEFAULT 1,
  created_at  TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO explore_streams (title,streamer,game,platform,url,emoji,viewers,status,sort_order) VALUES
('MPL ID Season Live','MPL Indonesia','Mobile Legends','youtube','https://www.youtube.com/@MPLIndonesiaOfficial','🏆','12.4K','live',1),
('Ranked Grind to Mythic','Pro Player','Mobile Legends','youtube','https://www.youtube.com','🎮','3.1K','live',2);

-- ── Kategori Explore (kartu kategori di pages/explore.php) ──
CREATE TABLE IF NOT EXISTS explore_categories (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cat_key     VARCHAR(40)   NOT NULL UNIQUE,
  label       VARCHAR(60)   NOT NULL,
  emoji       VARCHAR(10)   DEFAULT '📁',
  color       VARCHAR(40)   DEFAULT 'rgba(227,24,55,.6)',
  bg          VARCHAR(40)   DEFAULT 'rgba(227,24,55,.1)',
  badge       VARCHAR(40)   DEFAULT 'var(--red)',
  tag         VARCHAR(30)   DEFAULT NULL,
  description VARCHAR(150)  DEFAULT NULL,
  sort_order  SMALLINT      NOT NULL DEFAULT 0,
  is_active   TINYINT(1)    NOT NULL DEFAULT 1,
  created_at  TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO explore_categories (cat_key,label,emoji,color,bg,badge,tag,description,sort_order) VALUES
('artikel','Artikel','📰','rgba(227,24,55,.6)','rgba(227,24,55,.1)','var(--red)','Baru','Berita & review game terbaru',1),
('tips','Tips & Trik','💡','rgba(245,166,35,.6)','rgba(245,166,35,.1)','var(--gold)','Hot','Panduan & strategi bermain',2),
('turnamen','Turnamen','🏆','rgba(56,189,248,.6)','rgba(56,189,248,.1)','#38bdf8','Live','Jadwal & hasil kompetisi',3),
('komunitas','Komunitas','👥','rgba(45,212,191,.6)','rgba(45,212,191,.1)','#2dd4bf','Join','Forum & diskusi player',4),
('livescore','Livescore','📊','rgba(167,139,250,.6)','rgba(167,139,250,.1)','#a78bfa','Segera','Skor pertandingan real-time',5),
('review','Review Game','⭐','rgba(251,146,60,.6)','rgba(251,146,60,.1)','#fb923c','Pilihan','Ulasan game terpopuler',6),
('update','Update Game','🔔','rgba(34,211,160,.6)','rgba(34,211,160,.1)','#22d3a0','Update','Patch notes & update terbaru',7),
('promo','Promo & Event','🎁','rgba(227,24,55,.6)','rgba(227,24,55,.1)','var(--red)','Gratis','Event & giveaway eksklusif',8);

-- ── Konfigurasi Tier Reward (dibaca pages/reward.php) ──
INSERT INTO settings (`key`,`value`) VALUES
('reward_earn_per',     '1000'),
('reward_cb_bronze',    '0'),
('reward_min_silver',   '500000'),
('reward_cb_silver',    '1'),
('reward_min_gold',     '2000000'),
('reward_cb_gold',      '2'),
('reward_min_platinum', '5000000'),
('reward_cb_platinum',  '3')
ON DUPLICATE KEY UPDATE `value`=`value`;
