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
        category ENUM ('Express', 'Regular', 'Comfort', 'Suite') NOT NULL DEFAULT 'Regular',
        price DECIMAL(11, 2) NOT NULL,
        available INT NOT NULL,
        INDEX idx_rooms_category (category),
        CONSTRAINT chk_rooms_available CHECK (available >= 0),
        CONSTRAINT chk_rooms_price CHECK (price >= 0)
    ) ENGINE = InnoDB;

CREATE TABLE
    commodities (type VARCHAR(64) PRIMARY KEY) ENGINE = InnoDB;

INSERT INTO
    commodities
VALUES
    ("Baño integrado"),
    ("Wi-Fi"),
    ("Accesibilidad"),
    ("Televisor"),
    ("Aire acondicionado"),
    ("Escritorio"),
    ("Impresora/Escáner"),
    ("Clóset"),
    ("Caja fuerte");

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


INSERT INTO users (name, email, password, role)
VALUES
    ('Administrador', 'admin@gmail.com', '$2y$10$Xh8.eUU7IYKw7mY6xEnB4e6D4S/CUo5qeKnqJAkD7ZJc0kMy2D54i', 'admin'),
    ('Usuario', 'regular@gmail.com', '$2y$10$PTI31p0KHgWel./3cMxLNupFmZrYbhp7XpKlGQlUOos204mgc2iI2', 'regular'),
    ('Ana García', 'ana.garcia@gmail.com', '$2y$10$o4f9meXLPttanlLo950ocueLBCVPgUMGwsZrf.3zTQwmV7uoNcZdm', 'admin'),
    ('Andrés Cruz', 'andres.cruz@outlook.com', '$2y$10$01k26IbkTZRe1Cr2MfBTuuhWIsuetNGaKkJ30u.iAZGb1AoD1xoYa', 'regular'),
    ('Camila Sánchez', 'camila.sanchez@gmail.com', '$2y$10$TdRCoPjAaY8SWyutoTUKr.j..4bq3NZY93webxoOT26sROQ9G15FO', 'regular'),
    ('Carlos Hernández', 'carlos.hernandez@outlook.com', '$2y$10$uKO6Lv0lT49JQsloDWg5nuo6A7JcD3YiQhj8EYg8jR75bZg9f1aPK', 'admin'),
    ('Daniela Reyes', 'daniela.reyes@hotmail.com', '$2y$10$ppJcor446JEgfTvhFdoquudMEMEor/SmaJNDYwvdXE5pHRgQI5L8S', 'regular'),
    ('Diego Torres', 'diego.torres@outlook.com', '$2y$10$rdPxcaodYjxbpMRy5zlnP.aW.xn0OqwnXdAj57X6f7TsozcioULz.', 'regular');


INSERT INTO
    rooms (name, description, category, price, available)
VALUES
    (
        'Ejecutiva Sencilla',
        'Esta habitación individual ofrece una configuración funcional para viajes corporativos. La habitación incluye cama, escritorio de trabajo, silla funcional, iluminación adecuada y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión y espacio para equipaje. Su distribución permite organizar documentos y dispositivos sin afectar la circulación. El servicio de limpieza mantiene el espacio en condiciones apropiadas. El equipamiento cubre las necesidades habituales de alojamiento, trabajo ocasional y descanso durante reuniones, capacitaciones y viajes de negocios en el ámbito empresarial.',
        'Regular',
        9999.00,
        13
    ),
    (
        'Ejecutiva Matrimonial',
        'Esta habitación matrimonial facilita el descanso durante visitas profesionales individuales. La habitación incluye cama, escritorio de trabajo, silla funcional, iluminación adecuada y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión y espacio para equipaje. Su distribución permite organizar documentos y dispositivos sin afectar la circulación. El servicio de limpieza mantiene el espacio en condiciones apropiadas. El equipamiento cubre las necesidades habituales de alojamiento, trabajo ocasional y descanso durante reuniones, capacitaciones y viajes de negocios en el ámbito empresarial.',
        'Regular',
        5749.00,
        20
    ),

    (
        'Ejecutiva Individual ',
        'Esta habitación ejecutiva combina descanso y espacio para trabajar cómodamente. La habitación incluye cama, escritorio de trabajo, silla funcional, iluminación adecuada y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión y espacio para equipaje. Su distribución permite organizar documentos y dispositivos sin afectar la circulación. El servicio de limpieza mantiene el espacio en condiciones apropiadas. El equipamiento cubre las necesidades habituales de alojamiento, trabajo ocasional y descanso durante reuniones, capacitaciones y viajes de negocios en el ámbito empresarial.',
        'Regular',
        4999.00,
        12
    ),
    (
        'Ejecutiva Doble',
        'Esta habitación está preparada para estancias profesionales con equipamiento básico. La habitación incluye cama, escritorio de trabajo, silla funcional, iluminación adecuada y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión y espacio para equipaje. Su distribución permite organizar documentos y dispositivos sin afectar la circulación. El servicio de limpieza mantiene el espacio en condiciones apropiadas. El equipamiento cubre las necesidades habituales de alojamiento, trabajo ocasional y descanso durante reuniones, capacitaciones y viajes de negocios en el ámbito empresarial.',
        'Regular',
        7249.00,
        8
    ),
    (
        'Express Doble',
        'Esta habitación express ofrece servicios esenciales para estancias profesionales breves. El espacio incorpora cama, escritorio compacto, silla funcional, iluminación de lectura y conexión inalámbrica a internet. Cuenta con baño privado, climatización, televisión y almacenamiento básico para equipaje. La distribución prioriza el aprovechamiento del espacio y facilita las actividades esenciales del huésped. El servicio de limpieza mantiene las instalaciones en condiciones adecuadas. Su equipamiento responde a estancias breves, escalas laborales, reuniones puntuales y visitas profesionales que requieren servicios prácticos durante viajes.',
        'Express',
        1999.00,
        10
    ),
    (
        'Express Matrimonial',
        'Esta opción compacta facilita alojamiento durante escalas y reuniones laborales. El espacio incorpora cama, escritorio compacto, silla funcional, iluminación de lectura y conexión inalámbrica a internet. Cuenta con baño privado, climatización, televisión y almacenamiento básico para equipaje. La distribución prioriza el aprovechamiento del espacio y facilita las actividades esenciales del huésped. El servicio de limpieza mantiene las instalaciones en condiciones adecuadas. Su equipamiento responde a estancias breves, escalas laborales, reuniones puntuales y visitas profesionales que requieren servicios prácticos durante viajes.',
        'Express',
        3249.00,
        18
    ),
    (
        'Express Individual',
        'Esta habitación está diseñada para visitas laborales breves y prácticas. El espacio incorpora cama, escritorio compacto, silla funcional, iluminación de lectura y conexión inalámbrica a internet. Cuenta con baño privado, climatización, televisión y almacenamiento básico para equipaje. La distribución prioriza el aprovechamiento del espacio y facilita las actividades esenciales del huésped. El servicio de limpieza mantiene las instalaciones en condiciones adecuadas. Su equipamiento responde a estancias breves, escalas laborales, reuniones puntuales y visitas profesionales que requieren servicios prácticos durante viajes.',
        'Express',
        5249.00,
        10
    ),
    (
        'Express Sencilla',
        'Esta habitación express proporciona solución funcional para viajeros de negocios. El espacio incorpora cama, escritorio compacto, silla funcional, iluminación de lectura y conexión inalámbrica a internet. Cuenta con baño privado, climatización, televisión y almacenamiento básico para equipaje. La distribución prioriza el aprovechamiento del espacio y facilita las actividades esenciales del huésped. El servicio de limpieza mantiene las instalaciones en condiciones adecuadas. Su equipamiento responde a estancias breves, escalas laborales, reuniones puntuales y visitas profesionales que requieren servicios prácticos durante viajes.',
        'Express',
        9749.00,
        13
    ),
    (
        'Comfort Ejecutiva',
        'Esta habitación comfort amplía el descanso durante viajes profesionales prolongados. El equipamiento incluye cama amplia, escritorio de trabajo, silla ergonómica, iluminación regulable y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión, espacio de almacenamiento y área para organizar pertenencias. La distribución favorece el trabajo ocasional y el descanso después de la jornada. El servicio de limpieza mantiene las instalaciones en condiciones apropiadas. Es adecuada para viajes corporativos de varios días y huéspedes que requieren comodidad durante su estancia.',
        'Comfort',
        2999.50,
        19
    ),
    (
        'Comfort Matrimonial',
        'Esta opción añade equipamiento para huéspedes que trabajan durante estancias. El equipamiento incluye cama amplia, escritorio de trabajo, silla ergonómica, iluminación regulable y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión, espacio de almacenamiento y área para organizar pertenencias. La distribución favorece el trabajo ocasional y el descanso después de la jornada. El servicio de limpieza mantiene las instalaciones en condiciones apropiadas. Es adecuada para viajes corporativos de varios días y huéspedes que requieren comodidad durante su estancia.',
        'Comfort',
        5999.00,
        17
    ),
    (
        'Comfort Doble',
        'Esta habitación combina actividades laborales y descanso con comodidad adicional. El equipamiento incluye cama amplia, escritorio de trabajo, silla ergonómica, iluminación regulable y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión, espacio de almacenamiento y área para organizar pertenencias. La distribución favorece el trabajo ocasional y el descanso después de la jornada. El servicio de limpieza mantiene las instalaciones en condiciones apropiadas. Es adecuada para viajes corporativos de varios días y huéspedes que requieren comodidad durante su estancia.',
        'Comfort',
        7999.00,
        15
    ),
    (
        'Comfort Sencilla',
        'Esta habitación comfort proporciona espacio funcional para estancias corporativas prolongadas. El equipamiento incluye cama amplia, escritorio de trabajo, silla ergonómica, iluminación regulable y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión, espacio de almacenamiento y área para organizar pertenencias. La distribución favorece el trabajo ocasional y el descanso después de la jornada. El servicio de limpieza mantiene las instalaciones en condiciones apropiadas. Es adecuada para viajes corporativos de varios días y huéspedes que requieren comodidad durante su estancia.',
        'Comfort',
        6249.00,
        23
    ),
    (
        'Suite Ejecutiva Sencilla',
        'Esta suite ejecutiva ofrece áreas para trabajo, descanso y reuniones. La suite integra áreas diferenciadas para descanso y trabajo, con cama amplia, escritorio independiente y zona de estar. Incluye conexión inalámbrica a internet, silla de trabajo, iluminación funcional, baño privado, climatización, televisión y espacio de almacenamiento. Su distribución permite recibir visitas o revisar documentos sin utilizar el área de descanso. El servicio de limpieza mantiene las instalaciones en condiciones. Es adecuada para directivos y viajeros que necesitan espacio y privacidad.',
        'Suite',
        4499.00,
        15
    ),
    (
        'Suite Ejecutiva Matrimonial',
        'Esta suite proporciona privacidad y espacio para estancias profesionales ejecutivas. La suite integra áreas diferenciadas para descanso y trabajo, con cama amplia, escritorio independiente y zona de estar. Incluye conexión inalámbrica a internet, silla de trabajo, iluminación funcional, baño privado, climatización, televisión y espacio de almacenamiento. Su distribución permite recibir visitas o revisar documentos sin utilizar el área de descanso. El servicio de limpieza mantiene las instalaciones en condiciones. Es adecuada para directivos y viajeros que necesitan espacio y privacidad.',
        'Suite',
        2499.00,
        15
    ),
    (
        'Suite Ejecutiva Corporativa',
        'Esta suite está diseñada para directivos en viajes corporativos prolongados. La suite integra áreas diferenciadas para descanso y trabajo, con cama amplia, escritorio independiente y zona de estar. Incluye conexión inalámbrica a internet, silla de trabajo, iluminación funcional, baño privado, climatización, televisión y espacio de almacenamiento. Su distribución permite recibir visitas o revisar documentos sin utilizar el área de descanso. El servicio de limpieza mantiene las instalaciones en condiciones. Es adecuada para directivos y viajeros que necesitan espacio y privacidad.',
        'Suite',
        6499.00,
        20
    ),
    (
        'Suite Ejecutiva Individual',
        'Esta suite facilita estancias prolongadas con espacios de trabajo independientes. La suite integra áreas diferenciadas para descanso y trabajo, con cama amplia, escritorio independiente y zona de estar. Incluye conexión inalámbrica a internet, silla de trabajo, iluminación funcional, baño privado, climatización, televisión y espacio de almacenamiento. Su distribución permite recibir visitas o revisar documentos sin utilizar el área de descanso. El servicio de limpieza mantiene las instalaciones en condiciones. Es adecuada para directivos y viajeros que necesitan espacio y privacidad.',
        'Suite',
        5499.00,
        19
    );


INSERT INTO room_commodities (room_uuid, type)
VALUES
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 0, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 0, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 0, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 0, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 1, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 1, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 1, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 1, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 2, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 2, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 2, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 3, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 3, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 3, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 3, 1), 'Clóset'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 4, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 4, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 4, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 4, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 4, 1), 'Impresora/Escáner'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 5, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 5, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 5, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 5, 1), 'Accesibilidad'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 6, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 6, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 6, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 6, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 6, 1), 'Clóset'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 6, 1), 'Caja fuerte'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 7, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 7, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 7, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 7, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 7, 1), 'Accesibilidad'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 8, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 8, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 8, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 8, 1), 'Impresora/Escáner'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 8, 1), 'Clóset'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 8, 1), 'Caja fuerte'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 9, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 9, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 9, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 9, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 9, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 9, 1), 'Impresora/Escáner'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 9, 1), 'Caja fuerte'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 10, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 10, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 10, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 10, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 10, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 10, 1), 'Impresora/Escáner'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 11, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 11, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 11, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 11, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 11, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 11, 1), 'Accesibilidad'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 11, 1), 'Impresora/Escáner'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 11, 1), 'Clóset'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 12, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 12, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 12, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 12, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 12, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 12, 1), 'Accesibilidad'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 12, 1), 'Impresora/Escáner'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 12, 1), 'Clóset'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 12, 1), 'Caja fuerte'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 13, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 13, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 13, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 13, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 13, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 13, 1), 'Accesibilidad'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 13, 1), 'Impresora/Escáner'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 13, 1), 'Caja fuerte'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 14, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 14, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 14, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 14, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 14, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 14, 1), 'Clóset'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 14, 1), 'Caja fuerte'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 15, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 15, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 15, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 15, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 15, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 15, 1), 'Accesibilidad'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 15, 1), 'Impresora/Escáner'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 15, 1), 'Clóset');

INSERT INTO images (room_uuid, url)
SELECT
    r.uuid,
    CONCAT('room_images/dummy', MOD((r.room_number - 1) * 2 + i.n, 12) + 1, '.jpg')
FROM (
    SELECT
        uuid,
        ROW_NUMBER() OVER (ORDER BY name) AS room_number
    FROM rooms
) AS r
CROSS JOIN (
    SELECT 0 AS n
    UNION ALL SELECT 1
) AS i;
