CREATE TABLE
    users (
        uuid CHAR(36) PRIMARY KEY DEFAULT (UUID ()),
        name VARCHAR(48) NOT NULL,
        email VARCHAR(96) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM ('regular', 'admin') NOT NULL DEFAULT 'regular'
    ) ENGINE = InnoDB;

CREATE TABLE
    rooms (
        uuid CHAR(36) PRIMARY KEY DEFAULT (UUID ()),
        name VARCHAR(255) UNIQUE NOT NULL,
        description VARCHAR(4095) NOT NULL,
        category ENUM ('express', 'regular', 'comfort', 'suite') NOT NULL DEFAULT 'regular',
        price DECIMAL(11, 2) NOT NULL,
        available INT NOT NULL,
        INDEX idx_rooms_category (category),
        CONSTRAINT chk_rooms_available CHECK (available >= 0),
        CONSTRAINT chk_rooms_price CHECK (price >= 0)
    ) ENGINE = InnoDB;

CREATE TABLE
    commodities (type VARCHAR(64) PRIMARY KEY) ENGINE = InnoDB;

CREATE TABLE
    room_commodities (
        room_uuid CHAR(36),
        type VARCHAR(64),
        PRIMARY KEY (room_uuid, type),
        INDEX idx_room_commodities_type (type),
        CONSTRAINT fk_room_commodities_room FOREIGN KEY (room_uuid) REFERENCES rooms (uuid) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_room_commodities_type FOREIGN KEY (type) REFERENCES commodities (type) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB;

CREATE TABLE
    user_rooms (
        user_uuid CHAR(36),
        room_uuid CHAR(36),
        quantity INT NOT NULL,
        PRIMARY KEY (user_uuid, room_uuid),
        INDEX idx_user_rooms_room (room_uuid),
        CONSTRAINT chk_user_rooms_quantity CHECK (quantity > 0),
        CONSTRAINT fk_user_rooms_user FOREIGN KEY (user_uuid) REFERENCES users (uuid) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_user_rooms_room FOREIGN KEY (room_uuid) REFERENCES rooms (uuid) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB;

CREATE TABLE
    images (
        uuid CHAR(36) PRIMARY KEY DEFAULT (UUID ()),
        room_uuid CHAR(36) NOT NULL,
        url VARCHAR(512) NOT NULL,
        INDEX idx_images_room (room_uuid),
        CONSTRAINT fk_images_room FOREIGN KEY (room_uuid) REFERENCES rooms (uuid) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB;