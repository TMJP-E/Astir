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
    ('Ana García', 'ana.garcia@gmail.com', '$2y$10$o4f9meXLPttanlLo950ocueLBCVPgUMGwsZrf.3zTQwmV7uoNcZdm', 'admin'),
    ('Andrés Cruz', 'andres.cruz@outlook.com', '$2y$10$01k26IbkTZRe1Cr2MfBTuuhWIsuetNGaKkJ30u.iAZGb1AoD1xoYa', 'regular'),
    ('Camila Sánchez', 'camila.sanchez@gmail.com', '$2y$10$TdRCoPjAaY8SWyutoTUKr.j..4bq3NZY93webxoOT26sROQ9G15FO', 'regular'),
    ('Carlos Hernández', 'carlos.hernandez@outlook.com', '$2y$10$uKO6Lv0lT49JQsloDWg5nuo6A7JcD3YiQhj8EYg8jR75bZg9f1aPK', 'admin'),
    ('Daniela Reyes', 'daniela.reyes@hotmail.com', '$2y$10$ppJcor446JEgfTvhFdoquudMEMEor/SmaJNDYwvdXE5pHRgQI5L8S', 'regular'),
    ('Diego Torres', 'diego.torres@outlook.com', '$2y$10$rdPxcaodYjxbpMRy5zlnP.aW.xn0OqwnXdAj57X6f7TsozcioULz.', 'regular'),
    ('Emiliano Morales', 'emiliano.morales@outlook.com', '$2y$10$4fcmAqKo3nPCp4oEuKQwOO4dSks816Ec11BI.P/fFpviXAgEWzD1y', 'regular'),
    ('Fernando Gómez', 'fernando.gomez@yahoo.com', '$2y$10$jrIOwNSRANj4aHrnOYkWl..72q/it41raZuJcHRL2UCCHl0N2wUd2', 'regular'),
    ('Javier Castillo', 'javier.castillo@yahoo.com', '$2y$10$FjDCFUNYIsQ6XPCaf6YZzu8LX63JkumMSK1aPPFbzRXQ4Gnn2L.sq', 'regular'),
    ('José Martínez', 'jose.martinez@yahoo.com', '$2y$10$XXKOLzkx2BMNWVyV78P.LeMU.aIH1.woy88LGZ88roI17UWCXppu6', 'admin'),
    ('Lucía Díaz', 'lucia.diaz@gmail.com', '$2y$10$OruVtc1KoS76S78OpqM2COHDvsYMMfuZtYWb3tCB3jOcnQuypmeY.', 'regular'),
    ('María Fernanda López', 'maria.lopez@hotmail.com', '$2y$10$OG1P.T7NoUYTGlITrhxJhO8oTRh6h95.99kMxB8m.aa29aG9ZV3oq', 'admin'),
    ('Miguel Ángel Rivera', 'miguel.rivera@yahoo.com', '$2y$10$wfhHOHiCtkZO.Z2M2DP58ekEkxE9AYHHbighGX0/XuKSQSYUCCQW2', 'regular'),
    ('Regina Ortiz', 'regina.ortiz@hotmail.com', '$2y$10$9dnTQaApDh0iJMd/NVDkRu0hBwsCNl8rB4aB9zl7OmmtmdAyZnzsq', 'regular'),
    ('Sofía Ramírez', 'sofia.ramirez@gmail.com', '$2y$10$AS4YUpJzzxItOanu0P1MyuncIqfvbLm.qqFZ/sq779ZyOFs2OiCA6', 'regular'),
    ('Valentina Flores', 'valentina.flores@hotmail.com', '$2y$10$kduktZREwHqXQUPYnw17MeksECIhlOEH/DWV5djIUxA5rE8.qjgcG', 'regular');


INSERT INTO
    rooms (name, description, category, price, available)
VALUES
    (
        'Habitación Ejecutiva Individual 01',
        'Esta habitación individual ofrece una configuración funcional para viajes corporativos. La habitación incluye cama, escritorio de trabajo, silla funcional, iluminación adecuada y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión y espacio para equipaje. Su distribución permite organizar documentos y dispositivos sin afectar la circulación. El servicio de limpieza mantiene el espacio en condiciones apropiadas. El equipamiento cubre las necesidades habituales de alojamiento, trabajo ocasional y descanso durante reuniones, capacitaciones y viajes de negocios en el ámbito empresarial.',
        'regular',
        9999.00,
        7
    ),
    (
        'Habitación Ejecutiva Matrimonial 01',
        'Esta habitación matrimonial facilita el descanso durante visitas profesionales individuales. La habitación incluye cama, escritorio de trabajo, silla funcional, iluminación adecuada y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión y espacio para equipaje. Su distribución permite organizar documentos y dispositivos sin afectar la circulación. El servicio de limpieza mantiene el espacio en condiciones apropiadas. El equipamiento cubre las necesidades habituales de alojamiento, trabajo ocasional y descanso durante reuniones, capacitaciones y viajes de negocios en el ámbito empresarial.',
        'regular',
        5749.00,
        20
    ),

    (
        'Habitación Ejecutiva Individual 02',
        'Esta habitación ejecutiva combina descanso y espacio para trabajar cómodamente. La habitación incluye cama, escritorio de trabajo, silla funcional, iluminación adecuada y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión y espacio para equipaje. Su distribución permite organizar documentos y dispositivos sin afectar la circulación. El servicio de limpieza mantiene el espacio en condiciones apropiadas. El equipamiento cubre las necesidades habituales de alojamiento, trabajo ocasional y descanso durante reuniones, capacitaciones y viajes de negocios en el ámbito empresarial.',
        'regular',
        4999.00,
        9
    ),
    (
        'Habitación Ejecutiva Matrimonial 02',
        'Esta habitación está preparada para estancias profesionales con equipamiento básico. La habitación incluye cama, escritorio de trabajo, silla funcional, iluminación adecuada y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión y espacio para equipaje. Su distribución permite organizar documentos y dispositivos sin afectar la circulación. El servicio de limpieza mantiene el espacio en condiciones apropiadas. El equipamiento cubre las necesidades habituales de alojamiento, trabajo ocasional y descanso durante reuniones, capacitaciones y viajes de negocios en el ámbito empresarial.',
        'regular',
        7249.00,
        2
    ),
    (
        'Habitación Ejecutiva Doble 02',
        'Esta opción responde a necesidades habituales de viajeros de negocios. La habitación incluye cama, escritorio de trabajo, silla funcional, iluminación adecuada y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión y espacio para equipaje. Su distribución permite organizar documentos y dispositivos sin afectar la circulación. El servicio de limpieza mantiene el espacio en condiciones apropiadas. El equipamiento cubre las necesidades habituales de alojamiento, trabajo ocasional y descanso durante reuniones, capacitaciones y viajes de negocios en el ámbito empresarial.',
        'regular',
        6749.00,
        4
    ),
    (
        'Habitación Ejecutiva Individual 03',
        'Esta habitación ofrece condiciones para huéspedes que viajan por trabajo. La habitación incluye cama, escritorio de trabajo, silla funcional, iluminación adecuada y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión y espacio para equipaje. Su distribución permite organizar documentos y dispositivos sin afectar la circulación. El servicio de limpieza mantiene el espacio en condiciones apropiadas. El equipamiento cubre las necesidades habituales de alojamiento, trabajo ocasional y descanso durante reuniones, capacitaciones y viajes de negocios en el ámbito empresarial.',
        'regular',
        2249.00,
        15
    ),
    (
        'Habitación Ejecutiva Matrimonial 03',
        'Esta habitación ofrece distribución adecuada para estancias corporativas de trabajo. La habitación incluye cama, escritorio de trabajo, silla funcional, iluminación adecuada y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión y espacio para equipaje. Su distribución permite organizar documentos y dispositivos sin afectar la circulación. El servicio de limpieza mantiene el espacio en condiciones apropiadas. El equipamiento cubre las necesidades habituales de alojamiento, trabajo ocasional y descanso durante reuniones, capacitaciones y viajes de negocios en el ámbito empresarial.',
        'regular',
        8499.50,
        12
    ),
    (
        'Habitación Ejecutiva Doble 03',
        'Esta habitación está orientada a profesionales que requieren servicios esenciales. La habitación incluye cama, escritorio de trabajo, silla funcional, iluminación adecuada y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión y espacio para equipaje. Su distribución permite organizar documentos y dispositivos sin afectar la circulación. El servicio de limpieza mantiene el espacio en condiciones apropiadas. El equipamiento cubre las necesidades habituales de alojamiento, trabajo ocasional y descanso durante reuniones, capacitaciones y viajes de negocios en el ámbito empresarial.',
        'regular',
        9499.50,
        15
    ),
    (
        'Habitación Ejecutiva Individual 04',
        'Esta habitación proporciona alojamiento funcional para reuniones y viajes empresariales. La habitación incluye cama, escritorio de trabajo, silla funcional, iluminación adecuada y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión y espacio para equipaje. Su distribución permite organizar documentos y dispositivos sin afectar la circulación. El servicio de limpieza mantiene el espacio en condiciones apropiadas. El equipamiento cubre las necesidades habituales de alojamiento, trabajo ocasional y descanso durante reuniones, capacitaciones y viajes de negocios en el ámbito empresarial.',
        'regular',
        9249.00,
        6
    ),
    (
        'Habitación Express Individual 01',
        'Esta habitación express ofrece servicios esenciales para estancias profesionales breves. El espacio incorpora cama, escritorio compacto, silla funcional, iluminación de lectura y conexión inalámbrica a internet. Cuenta con baño privado, climatización, televisión y almacenamiento básico para equipaje. La distribución prioriza el aprovechamiento del espacio y facilita las actividades esenciales del huésped. El servicio de limpieza mantiene las instalaciones en condiciones adecuadas. Su equipamiento responde a estancias breves, escalas laborales, reuniones puntuales y visitas profesionales que requieren servicios prácticos durante viajes.',
        'express',
        1999.00,
        6
    ),
    (
        'Habitación Express Matrimonial 01',
        'Esta opción compacta facilita alojamiento durante escalas y reuniones laborales. El espacio incorpora cama, escritorio compacto, silla funcional, iluminación de lectura y conexión inalámbrica a internet. Cuenta con baño privado, climatización, televisión y almacenamiento básico para equipaje. La distribución prioriza el aprovechamiento del espacio y facilita las actividades esenciales del huésped. El servicio de limpieza mantiene las instalaciones en condiciones adecuadas. Su equipamiento responde a estancias breves, escalas laborales, reuniones puntuales y visitas profesionales que requieren servicios prácticos durante viajes.',
        'express',
        3249.00,
        18
    ),
    (
        'Habitación Express Doble 01',
        'Esta habitación está diseñada para visitas laborales breves y prácticas. El espacio incorpora cama, escritorio compacto, silla funcional, iluminación de lectura y conexión inalámbrica a internet. Cuenta con baño privado, climatización, televisión y almacenamiento básico para equipaje. La distribución prioriza el aprovechamiento del espacio y facilita las actividades esenciales del huésped. El servicio de limpieza mantiene las instalaciones en condiciones adecuadas. Su equipamiento responde a estancias breves, escalas laborales, reuniones puntuales y visitas profesionales que requieren servicios prácticos durante viajes.',
        'express',
        5249.00,
        10
    ),
    (
        'Habitación Express Individual 02',
        'Esta habitación express proporciona solución funcional para viajeros de negocios. El espacio incorpora cama, escritorio compacto, silla funcional, iluminación de lectura y conexión inalámbrica a internet. Cuenta con baño privado, climatización, televisión y almacenamiento básico para equipaje. La distribución prioriza el aprovechamiento del espacio y facilita las actividades esenciales del huésped. El servicio de limpieza mantiene las instalaciones en condiciones adecuadas. Su equipamiento responde a estancias breves, escalas laborales, reuniones puntuales y visitas profesionales que requieren servicios prácticos durante viajes.',
        'express',
        9749.00,
        13
    ),
    (
        'Habitación Express Matrimonial 02',
        'Esta configuración prioriza servicios básicos para huéspedes con agendas ajustadas. El espacio incorpora cama, escritorio compacto, silla funcional, iluminación de lectura y conexión inalámbrica a internet. Cuenta con baño privado, climatización, televisión y almacenamiento básico para equipaje. La distribución prioriza el aprovechamiento del espacio y facilita las actividades esenciales del huésped. El servicio de limpieza mantiene las instalaciones en condiciones adecuadas. Su equipamiento responde a estancias breves, escalas laborales, reuniones puntuales y visitas profesionales que requieren servicios prácticos durante viajes.',
        'express',
        8749.50,
        9
    ),
    (
        'Habitación Express Doble 02',
        'Esta habitación permite descansar entre compromisos profesionales en la ciudad. El espacio incorpora cama, escritorio compacto, silla funcional, iluminación de lectura y conexión inalámbrica a internet. Cuenta con baño privado, climatización, televisión y almacenamiento básico para equipaje. La distribución prioriza el aprovechamiento del espacio y facilita las actividades esenciales del huésped. El servicio de limpieza mantiene las instalaciones en condiciones adecuadas. Su equipamiento responde a estancias breves, escalas laborales, reuniones puntuales y visitas profesionales que requieren servicios prácticos durante viajes.',
        'express',
        3749.50,
        6
    ),
    (
        'Habitación Express Individual 03',
        'Esta opción resulta adecuada para estancias breves por actividades empresariales. El espacio incorpora cama, escritorio compacto, silla funcional, iluminación de lectura y conexión inalámbrica a internet. Cuenta con baño privado, climatización, televisión y almacenamiento básico para equipaje. La distribución prioriza el aprovechamiento del espacio y facilita las actividades esenciales del huésped. El servicio de limpieza mantiene las instalaciones en condiciones adecuadas. Su equipamiento responde a estancias breves, escalas laborales, reuniones puntuales y visitas profesionales que requieren servicios prácticos durante viajes.',
        'express',
        4749.00,
        18
    ),
    (
        'Habitación Express Matrimonial 03',
        'Esta habitación ofrece equipamiento esencial para viajes de corta duración. El espacio incorpora cama, escritorio compacto, silla funcional, iluminación de lectura y conexión inalámbrica a internet. Cuenta con baño privado, climatización, televisión y almacenamiento básico para equipaje. La distribución prioriza el aprovechamiento del espacio y facilita las actividades esenciales del huésped. El servicio de limpieza mantiene las instalaciones en condiciones adecuadas. Su equipamiento responde a estancias breves, escalas laborales, reuniones puntuales y visitas profesionales que requieren servicios prácticos durante viajes.',
        'express',
        4249.50,
        19
    ),
    (
        'Habitación Express Doble 03',
        'Esta alternativa compacta responde a necesidades de visitas corporativas puntuales. El espacio incorpora cama, escritorio compacto, silla funcional, iluminación de lectura y conexión inalámbrica a internet. Cuenta con baño privado, climatización, televisión y almacenamiento básico para equipaje. La distribución prioriza el aprovechamiento del espacio y facilita las actividades esenciales del huésped. El servicio de limpieza mantiene las instalaciones en condiciones adecuadas. Su equipamiento responde a estancias breves, escalas laborales, reuniones puntuales y visitas profesionales que requieren servicios prácticos durante viajes.',
        'express',
        3499.50,
        5
    ),
    (
        'Habitación Express Individual 04',
        'Esta habitación ofrece alojamiento práctico para huéspedes con agendas laborales. El espacio incorpora cama, escritorio compacto, silla funcional, iluminación de lectura y conexión inalámbrica a internet. Cuenta con baño privado, climatización, televisión y almacenamiento básico para equipaje. La distribución prioriza el aprovechamiento del espacio y facilita las actividades esenciales del huésped. El servicio de limpieza mantiene las instalaciones en condiciones adecuadas. Su equipamiento responde a estancias breves, escalas laborales, reuniones puntuales y visitas profesionales que requieren servicios prácticos durante viajes.',
        'express',
        3999.00,
        14
    ),
    (
        'Habitación Comfort Ejecutiva 01',
        'Esta habitación comfort amplía el descanso durante viajes profesionales prolongados. El equipamiento incluye cama amplia, escritorio de trabajo, silla ergonómica, iluminación regulable y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión, espacio de almacenamiento y área para organizar pertenencias. La distribución favorece el trabajo ocasional y el descanso después de la jornada. El servicio de limpieza mantiene las instalaciones en condiciones apropiadas. Es adecuada para viajes corporativos de varios días y huéspedes que requieren comodidad durante su estancia.',
        'comfort',
        2999.50,
        19
    ),
    (
        'Habitación Comfort Matrimonial 01',
        'Esta opción añade equipamiento para huéspedes que trabajan durante estancias. El equipamiento incluye cama amplia, escritorio de trabajo, silla ergonómica, iluminación regulable y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión, espacio de almacenamiento y área para organizar pertenencias. La distribución favorece el trabajo ocasional y el descanso después de la jornada. El servicio de limpieza mantiene las instalaciones en condiciones apropiadas. Es adecuada para viajes corporativos de varios días y huéspedes que requieren comodidad durante su estancia.',
        'comfort',
        5999.00,
        7
    ),
    (
        'Habitación Comfort Doble 01',
        'Esta habitación combina actividades laborales y descanso con comodidad adicional. El equipamiento incluye cama amplia, escritorio de trabajo, silla ergonómica, iluminación regulable y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión, espacio de almacenamiento y área para organizar pertenencias. La distribución favorece el trabajo ocasional y el descanso después de la jornada. El servicio de limpieza mantiene las instalaciones en condiciones apropiadas. Es adecuada para viajes corporativos de varios días y huéspedes que requieren comodidad durante su estancia.',
        'comfort',
        7999.00,
        15
    ),
    (
        'Habitación Comfort Ejecutiva 02',
        'Esta habitación comfort proporciona espacio funcional para estancias corporativas prolongadas. El equipamiento incluye cama amplia, escritorio de trabajo, silla ergonómica, iluminación regulable y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión, espacio de almacenamiento y área para organizar pertenencias. La distribución favorece el trabajo ocasional y el descanso después de la jornada. El servicio de limpieza mantiene las instalaciones en condiciones apropiadas. Es adecuada para viajes corporativos de varios días y huéspedes que requieren comodidad durante su estancia.',
        'comfort',
        6249.00,
        3
    ),
    (
        'Habitación Comfort Matrimonial 02',
        'Esta configuración incorpora elementos para facilitar trabajo y descanso diario. El equipamiento incluye cama amplia, escritorio de trabajo, silla ergonómica, iluminación regulable y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión, espacio de almacenamiento y área para organizar pertenencias. La distribución favorece el trabajo ocasional y el descanso después de la jornada. El servicio de limpieza mantiene las instalaciones en condiciones apropiadas. Es adecuada para viajes corporativos de varios días y huéspedes que requieren comodidad durante su estancia.',
        'comfort',
        7749.50,
        19
    ),
    (
        'Habitación Comfort Doble 02',
        'Esta habitación ofrece condiciones superiores para profesionales que requieren comodidad. El equipamiento incluye cama amplia, escritorio de trabajo, silla ergonómica, iluminación regulable y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión, espacio de almacenamiento y área para organizar pertenencias. La distribución favorece el trabajo ocasional y el descanso después de la jornada. El servicio de limpieza mantiene las instalaciones en condiciones apropiadas. Es adecuada para viajes corporativos de varios días y huéspedes que requieren comodidad durante su estancia.',
        'comfort',
        8999.00,
        11
    ),
    (
        'Habitación Comfort Ejecutiva 03',
        'Esta alternativa es adecuada para visitas empresariales que requieren espacio. El equipamiento incluye cama amplia, escritorio de trabajo, silla ergonómica, iluminación regulable y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión, espacio de almacenamiento y área para organizar pertenencias. La distribución favorece el trabajo ocasional y el descanso después de la jornada. El servicio de limpieza mantiene las instalaciones en condiciones apropiadas. Es adecuada para viajes corporativos de varios días y huéspedes que requieren comodidad durante su estancia.',
        'comfort',
        8249.00,
        11
    ),
    (
        'Habitación Comfort Matrimonial 03',
        'Esta habitación comfort facilita organizar trabajo y pertenencias durante estancias. El equipamiento incluye cama amplia, escritorio de trabajo, silla ergonómica, iluminación regulable y conexión inalámbrica a internet. Dispone de baño privado, climatización, televisión, espacio de almacenamiento y área para organizar pertenencias. La distribución favorece el trabajo ocasional y el descanso después de la jornada. El servicio de limpieza mantiene las instalaciones en condiciones apropiadas. Es adecuada para viajes corporativos de varios días y huéspedes que requieren comodidad durante su estancia.',
        'comfort',
        2749.00,
        14
    ),
    (
        'Suite Ejecutiva 01',
        'Esta suite ejecutiva ofrece áreas para trabajo, descanso y reuniones. La suite integra áreas diferenciadas para descanso y trabajo, con cama amplia, escritorio independiente y zona de estar. Incluye conexión inalámbrica a internet, silla de trabajo, iluminación funcional, baño privado, climatización, televisión y espacio de almacenamiento. Su distribución permite recibir visitas o revisar documentos sin utilizar el área de descanso. El servicio de limpieza mantiene las instalaciones en condiciones. Es adecuada para directivos y viajeros que necesitan espacio y privacidad.',
        'suite',
        4499.00,
        5
    ),
    (
        'Suite Ejecutiva Matrimonial 01',
        'Esta suite proporciona privacidad y espacio para estancias profesionales ejecutivas. La suite integra áreas diferenciadas para descanso y trabajo, con cama amplia, escritorio independiente y zona de estar. Incluye conexión inalámbrica a internet, silla de trabajo, iluminación funcional, baño privado, climatización, televisión y espacio de almacenamiento. Su distribución permite recibir visitas o revisar documentos sin utilizar el área de descanso. El servicio de limpieza mantiene las instalaciones en condiciones. Es adecuada para directivos y viajeros que necesitan espacio y privacidad.',
        'suite',
        2499.00,
        15
    ),
    (
        'Suite Ejecutiva Corporativa 01',
        'Esta suite está diseñada para directivos en viajes corporativos prolongados. La suite integra áreas diferenciadas para descanso y trabajo, con cama amplia, escritorio independiente y zona de estar. Incluye conexión inalámbrica a internet, silla de trabajo, iluminación funcional, baño privado, climatización, televisión y espacio de almacenamiento. Su distribución permite recibir visitas o revisar documentos sin utilizar el área de descanso. El servicio de limpieza mantiene las instalaciones en condiciones. Es adecuada para directivos y viajeros que necesitan espacio y privacidad.',
        'suite',
        6499.00,
        20
    ),
    (
        'Suite Ejecutiva 02',
        'Esta suite facilita estancias prolongadas con espacios de trabajo independientes. La suite integra áreas diferenciadas para descanso y trabajo, con cama amplia, escritorio independiente y zona de estar. Incluye conexión inalámbrica a internet, silla de trabajo, iluminación funcional, baño privado, climatización, televisión y espacio de almacenamiento. Su distribución permite recibir visitas o revisar documentos sin utilizar el área de descanso. El servicio de limpieza mantiene las instalaciones en condiciones. Es adecuada para directivos y viajeros que necesitan espacio y privacidad.',
        'suite',
        5499.00,
        19
    );


INSERT INTO room_commodities (room_uuid, type)
VALUES
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 0, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 0, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 0, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 0, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 0, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 0, 1), 'Accesibilidad'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 1, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 1, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 1, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 1, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 1, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 1, 1), 'Accesibilidad'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 1, 1), 'Clóset'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 2, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 2, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 2, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 2, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 2, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 3, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 3, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 3, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 3, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 3, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 3, 1), 'Clóset'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 4, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 4, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 4, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 4, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 4, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 4, 1), 'Impresora/Escáner'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 4, 1), 'Caja fuerte'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 5, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 5, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 5, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 5, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 5, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 5, 1), 'Accesibilidad'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 5, 1), 'Caja fuerte'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 6, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 6, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 6, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 6, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 6, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 6, 1), 'Clóset'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 6, 1), 'Caja fuerte'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 7, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 7, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 7, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 7, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 7, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 7, 1), 'Accesibilidad'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 7, 1), 'Clóset'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 8, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 8, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 8, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 8, 1), 'Aire acondicionado'),
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
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 15, 1), 'Clóset'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 16, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 16, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 16, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 16, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 16, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 16, 1), 'Impresora/Escáner'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 17, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 17, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 17, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 17, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 17, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 17, 1), 'Clóset'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 17, 1), 'Caja fuerte'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 18, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 18, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 18, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 18, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 18, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 18, 1), 'Clóset'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 18, 1), 'Caja fuerte'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 19, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 19, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 19, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 19, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 19, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 19, 1), 'Accesibilidad'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 19, 1), 'Impresora/Escáner'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 19, 1), 'Clóset'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 20, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 20, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 20, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 20, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 20, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 20, 1), 'Accesibilidad'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 20, 1), 'Clóset'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 21, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 21, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 21, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 21, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 21, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 21, 1), 'Accesibilidad'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 21, 1), 'Caja fuerte'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 22, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 22, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 22, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 22, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 22, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 22, 1), 'Impresora/Escáner'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 23, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 23, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 23, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 23, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 23, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 24, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 24, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 24, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 24, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 24, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 24, 1), 'Impresora/Escáner'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 24, 1), 'Clóset'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 24, 1), 'Caja fuerte'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 25, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 25, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 25, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 25, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 25, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 25, 1), 'Impresora/Escáner'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 26, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 26, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 26, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 26, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 26, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 26, 1), 'Accesibilidad'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 27, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 27, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 27, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 27, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 27, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 27, 1), 'Accesibilidad'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 27, 1), 'Impresora/Escáner'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 27, 1), 'Clóset'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 28, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 28, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 28, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 28, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 28, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 28, 1), 'Caja fuerte'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 29, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 29, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 29, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 29, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 29, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 29, 1), 'Accesibilidad'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 29, 1), 'Impresora/Escáner'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 29, 1), 'Caja fuerte'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 30, 1), 'Baño integrado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 30, 1), 'Wi-Fi'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 30, 1), 'Televisor'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 30, 1), 'Aire acondicionado'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 30, 1), 'Escritorio'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 30, 1), 'Accesibilidad'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 30, 1), 'Impresora/Escáner'),
    ((SELECT uuid FROM rooms ORDER BY name LIMIT 30, 1), 'Caja fuerte');

INSERT INTO user_rooms (user_uuid, room_uuid, quantity)
VALUES
    ((SELECT uuid FROM users ORDER BY email LIMIT 0, 1), (SELECT uuid FROM rooms ORDER BY name LIMIT 0, 1), 1),
    ((SELECT uuid FROM users ORDER BY email LIMIT 0, 1), (SELECT uuid FROM rooms ORDER BY name LIMIT 8, 1), 2),
    ((SELECT uuid FROM users ORDER BY email LIMIT 0, 1), (SELECT uuid FROM rooms ORDER BY name LIMIT 16, 1), 1),
    ((SELECT uuid FROM users ORDER BY email LIMIT 0, 1), (SELECT uuid FROM rooms ORDER BY name LIMIT 24, 1), 3),
    ((SELECT uuid FROM users ORDER BY email LIMIT 1, 1), (SELECT uuid FROM rooms ORDER BY name LIMIT 1, 1), 4),
    ((SELECT uuid FROM users ORDER BY email LIMIT 1, 1), (SELECT uuid FROM rooms ORDER BY name LIMIT 9, 1), 3),
    ((SELECT uuid FROM users ORDER BY email LIMIT 1, 1), (SELECT uuid FROM rooms ORDER BY name LIMIT 17, 1), 2),
    ((SELECT uuid FROM users ORDER BY email LIMIT 1, 1), (SELECT uuid FROM rooms ORDER BY name LIMIT 25, 1), 1),
    ((SELECT uuid FROM users ORDER BY email LIMIT 2, 1), (SELECT uuid FROM rooms ORDER BY name LIMIT 2, 1), 2),
    ((SELECT uuid FROM users ORDER BY email LIMIT 2, 1), (SELECT uuid FROM rooms ORDER BY name LIMIT 10, 1), 4),
    ((SELECT uuid FROM users ORDER BY email LIMIT 2, 1), (SELECT uuid FROM rooms ORDER BY name LIMIT 18, 1), 3),
    ((SELECT uuid FROM users ORDER BY email LIMIT 2, 1), (SELECT uuid FROM rooms ORDER BY name LIMIT 26, 1), 1),
    ((SELECT uuid FROM users ORDER BY email LIMIT 3, 1), (SELECT uuid FROM rooms ORDER BY name LIMIT 3, 1), 3),
    ((SELECT uuid FROM users ORDER BY email LIMIT 3, 1), (SELECT uuid FROM rooms ORDER BY name LIMIT 11, 1), 2),
    ((SELECT uuid FROM users ORDER BY email LIMIT 3, 1), (SELECT uuid FROM rooms ORDER BY name LIMIT 19, 1), 1),
    ((SELECT uuid FROM users ORDER BY email LIMIT 3, 1), (SELECT uuid FROM rooms ORDER BY name LIMIT 27, 1), 4),
    ((SELECT uuid FROM users ORDER BY email LIMIT 4, 1), (SELECT uuid FROM rooms ORDER BY name LIMIT 4, 1), 1),
    ((SELECT uuid FROM users ORDER BY email LIMIT 4, 1), (SELECT uuid FROM rooms ORDER BY name LIMIT 12, 1), 3),
    ((SELECT uuid FROM users ORDER BY email LIMIT 4, 1), (SELECT uuid FROM rooms ORDER BY name LIMIT 20, 1), 2);

INSERT INTO images (room_uuid, url)
SELECT
    r.uuid,
    CONCAT('/room_images/dummy', i.image_number, '.jpg')
FROM (
    SELECT
        uuid,
        ROW_NUMBER() OVER (ORDER BY name) AS room_number
    FROM (
        SELECT uuid, name
        FROM rooms
        ORDER BY name
        LIMIT 0, 10
    ) AS selected_rooms
) AS r
JOIN (
    SELECT 1 AS room_number, 1 AS image_number
    UNION ALL SELECT 1, 2
    UNION ALL SELECT 2, 3
    UNION ALL SELECT 2, 4
    UNION ALL SELECT 3, 5
    UNION ALL SELECT 3, 6
    UNION ALL SELECT 4, 7
    UNION ALL SELECT 4, 8
    UNION ALL SELECT 5, 9
    UNION ALL SELECT 5, 10
    UNION ALL SELECT 6, 11
    UNION ALL SELECT 6, 12
    UNION ALL SELECT 7, 13
    UNION ALL SELECT 7, 14
    UNION ALL SELECT 8, 15
    UNION ALL SELECT 8, 16
    UNION ALL SELECT 9, 17
    UNION ALL SELECT 9, 18
    UNION ALL SELECT 10, 19
    UNION ALL SELECT 10, 20
) AS i
    ON r.room_number = i.room_number
