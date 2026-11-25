<?php

include_once __DIR__ . "/../config.inc.php";
include_once __DIR__ . "/dataEntities.php";
include_once __DIR__ . "/inputValidation.php";


/**
 * Opens a connection to the project's MariaDB database.
 *
 * Connection settings are read from the global variables loaded from
 * config.inc.php. The returned connection uses the utf8mb4 character set.
 * The caller is responsible for closing the returned connection.
 *
 * @return mysqli An open database connection.
 * @throws RuntimeException If the connection cannot be established or its character set cannot be configured.
 */
function connectDatabase(): mysqli
{
    $connection = null;
    try {
        $connection = mysqli_connect(
            $GLOBALS["servidor"],
            $GLOBALS["usuario"],
            $GLOBALS["contrasena"],
            $GLOBALS["base_datos"]
        );
        if (!$connection) {
            throw new RuntimeException("No fue posible establecer la conexión con la base de datos.");
        }
        if (!mysqli_set_charset($connection, "utf8mb4")) {
            mysqli_close($connection);
            throw new RuntimeException("No fue posible configurar la codificación de la conexión.");
        }
        return $connection;
    } catch (mysqli_sql_exception $exception) {
        if ($connection instanceof mysqli) {
            mysqli_close($connection);
        }
        throw new RuntimeException("Ocurrió un error al conectar con la base de datos.", 0, $exception);
    }
}

/**
 * Executes a static SELECT query and converts every result row into an object.
 *
 * This is a shared retrieval helper used by the functions that return lists
 * of entities. The constructor callback receives one associative database
 * row and must return the object to include in the result. Pass only trusted,
 * application-defined SQL here; this function does not accept query parameters.
 *
 * @param string $query A complete SQL query that returns rows.
 * @param callable $constructor A callback that converts an associative row into an object.
 * @return array<int, mixed> The objects created from the returned rows; an empty array if no rows match.
 * @throws RuntimeException If the query cannot be executed or its result cannot be retrieved.
 */
function fetchAllObjects(string $query, callable $constructor): array
{
    $connection = connectDatabase();
    $result = null;
    $objects = [];
    try {
        $result = mysqli_query($connection, $query);
        if (!$result instanceof mysqli_result) {
            throw new RuntimeException("No fue posible recuperar los registros de la base de datos.");
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $objects[] = $constructor($row);
        }
        return $objects;
    } catch (mysqli_sql_exception $exception) {
        throw new RuntimeException("Ocurrió un error al consultar la base de datos.", 0, $exception);
    } finally {
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        mysqli_close($connection);
    }
}

/**
 * Retrieves every registered user as a User entity.
 *
 * Each returned object includes the stored password hash in its password
 * property. Never display, return to a browser, log, or otherwise expose that
 * value. Use this function only in trusted server-side code that needs the
 * complete user records.
 *
 * @return array<int, User> All users, or an empty array if the users table is empty.
 * @throws RuntimeException If the database query fails.
 * @throws ValidationException If a stored user record does not satisfy the User entity rules.
 */
function getAllUsers(): array
{
    return fetchAllObjects(
        "SELECT * FROM users",
        fn($row) => new User(
            $row["uuid"],
            $row["name"],
            $row["email"],
            $row["password"],
            $row["role"]
        )
    );
}

/**
 * Retrieves all rooms together with their associated commodities and image paths.
 *
 * Each result is a Room entity. Its getCommodities() and getImages() methods
 * contain the strings associated with that room in room_commodities and
 * images, respectively. Image values are stored paths/URLs, not image file
 * contents. Rooms without associated commodities or images receive empty arrays.
 *
 * @return array<int, Room> All rooms, or an empty array if no rooms exist.
 * @throws RuntimeException If any of the room, commodity, or image queries fails.
 * @throws ValidationException If a stored value does not satisfy the Room entity rules.
 */
function getAllRooms(): array
{
    $connection = connectDatabase();
    $roomsResult = null;
    $commoditiesResult = null;
    $imagesResult = null;
    try {
        $roomsResult = mysqli_query($connection, "SELECT * FROM rooms");
        if (!$roomsResult instanceof mysqli_result) {
            throw new RuntimeException("No fue posible obtener las habitaciones.");
        }
        $commoditiesResult = mysqli_query($connection, "SELECT room_uuid, type FROM room_commodities");
        if (!$commoditiesResult instanceof mysqli_result) {
            throw new RuntimeException("No fue posible obtener las comodidades de las habitaciones.");
        }
        $imagesResult = mysqli_query($connection, "SELECT room_uuid, url FROM images");
        if (!$imagesResult instanceof mysqli_result) {
            throw new RuntimeException("No fue posible obtener las imágenes de las habitaciones.");
        }
        $commoditiesByRoom = [];
        while ($row = mysqli_fetch_assoc($commoditiesResult)) {
            $commoditiesByRoom[$row["room_uuid"]][] = $row["type"];
        }
        $imagesByRoom = [];
        while ($row = mysqli_fetch_assoc($imagesResult)) {
            $imagesByRoom[$row["room_uuid"]][] = $row["url"];
        }
        $rooms = [];
        while ($row = mysqli_fetch_assoc($roomsResult)) {
            $uuid = $row["uuid"];
            $rooms[] = new Room(
                $uuid,
                $row["name"],
                $row["description"],
                $row["category"],
                (float) $row["price"],
                (int) $row["available"],
                $commoditiesByRoom[$uuid] ?? [],
                $imagesByRoom[$uuid] ?? []
            );
        }
        return $rooms;
    } catch (mysqli_sql_exception $exception) {
        throw new RuntimeException("Ocurrió un error al obtener las habitaciones de la base de datos.", 0, $exception);
    } finally {
        if ($roomsResult instanceof mysqli_result) {
            mysqli_free_result($roomsResult);
        }
        if ($commoditiesResult instanceof mysqli_result) {
            mysqli_free_result($commoditiesResult);
        }
        if ($imagesResult instanceof mysqli_result) {
            mysqli_free_result($imagesResult);
        }
        mysqli_close($connection);
    }
}

/**
 * Retrieves the complete catalog of available commodities.
 *
 * Each row is returned as a Commodity entity. This is the catalog of
 * commodity types; it is not a list of the commodities assigned to a
 * particular room.
 *
 * @return array<int, Commodity> All commodity types, or an empty array if the catalog is empty.
 * @throws RuntimeException If the database query fails.
 * @throws ValidationException If a stored commodity does not satisfy the Commodity entity rules.
 */
function getAllCommodities(): array
{
    return fetchAllObjects(
        "SELECT * FROM commodities",
        fn($row) => new Commodity($row["type"])
    );
}

/**
 * Retrieves every user-room reservation as a UserRoom entity.
 *
 * Each object contains the user UUID, room UUID, reserved quantity, user name,
 * and room name. The returned rows represent reservations, not the full room
 * inventory or the number of rooms currently available.
 *
 * @return array<int, UserRoom> All reservations, or an empty array if none exist.
 * @throws RuntimeException If the database query fails.
 * @throws ValidationException If a stored reservation does not satisfy the UserRoom entity rules.
 */
function getAllUserRooms(): array
{
    return fetchAllObjects(
        "SELECT user_rooms.user_uuid, user_rooms.room_uuid, user_rooms.quantity,

                users.name AS user_name, rooms.name AS room_name

         FROM user_rooms

         JOIN users ON user_rooms.user_uuid = users.uuid

         JOIN rooms ON user_rooms.room_uuid = rooms.uuid",
        fn($row) => new UserRoom(
            $row["user_uuid"],
            $row["room_uuid"],
            (int) $row["quantity"],
            $row["user_name"],
            $row["room_name"]
        )
    );
}

/** Reservation list for a guest account, or for the whole administration view. */
function getReservationRows(?string $userUuid): array
{
    $db = connectDatabase();
    try {
        $sql = "SELECT ur.user_uuid, ur.room_uuid, ur.quantity, u.name AS customer,
                       r.name, r.category, r.price,
                       (SELECT MIN(url) FROM images WHERE room_uuid = r.uuid) AS image
                FROM user_rooms ur JOIN users u ON u.uuid = ur.user_uuid
                JOIN rooms r ON r.uuid = ur.room_uuid";
        $params = [];
        if ($userUuid !== null) {
            $sql .= " WHERE ur.user_uuid = ?";
            $params[] = $userUuid;
        }
        return $db->execute_query($sql . " ORDER BY r.name, u.name", $params)->fetch_all(MYSQLI_ASSOC);
    } finally {
        $db->close();
    }
}

/**
 * Searches for rooms using any combination of category, commodity, price,
 * availability, and name filters.
 *
 * All supplied filters are combined: a room must satisfy every active filter.
 * When multiple commodity types are supplied, a room must have all distinct
 * requested commodities, not merely one of them. The minimum and maximum
 * prices are inclusive. The name query matches a substring of the room name.
 * Results are ordered by category, price, and availability, each ascending.
 *
 * Empty category or commodity arrays disable those filters. A null numeric
 * filter disables that filter, and an empty name query disables name matching.
 * Category values should match the rooms.category ENUM values; commodity
 * values should match existing entries in the commodities catalog.
 *
 * @param array<int, string> $categories Optional room categories to include.
 * @param array<int, string> $commodities Optional commodity types that every matching room must contain.
 * @param float|null $minPrice Inclusive minimum room price, or null for no minimum.
 * @param float|null $maxPrice Inclusive maximum room price, or null for no maximum.
 * @param int|null $minAvailability Minimum number of currently available rooms, or null for no minimum.
 * @param string $nameQuery Optional substring to search for in room names.
 * @return array<int, Room> Matching Room entities, including their commodities and image paths.
 * @throws RuntimeException If the search query fails.
 * @throws ValidationException If retrieved room data does not satisfy the Room entity rules.
 */
function searchRooms(
    array $categories = [],
    array $commodities = [],
    ?float $minPrice = null,
    ?float $maxPrice = null,
    ?int $minAvailability = null,
    string $nameQuery = ""
): array {
    $connection = connectDatabase();
    $result = null;
    try {
        $conditions = [];
        $parameters = [];
        if (!empty($categories)) {
            $placeholders = implode(", ", array_fill(0, count($categories), "?"));
            $conditions[] = "r.category IN ($placeholders)";
            foreach ($categories as $category) {
                $parameters[] = $category;
            }
        }
        if (!empty($commodities)) {
            $placeholders = implode(", ", array_fill(0, count($commodities), "?"));
            $conditions[] = "r.uuid IN (
                SELECT room_uuid
                FROM room_commodities
                WHERE type IN ($placeholders)
                GROUP BY room_uuid
                HAVING COUNT(DISTINCT type) = ?
            )";
            foreach ($commodities as $commodity) {
                $parameters[] = $commodity;
            }
            $parameters[] = count(array_unique($commodities));
        }
        if ($minPrice !== null) {
            $conditions[] = "r.price >= ?";
            $parameters[] = $minPrice;
        }
        if ($maxPrice !== null) {
            $conditions[] = "r.price <= ?";
            $parameters[] = $maxPrice;
        }
        if ($minAvailability !== null) {
            $conditions[] = "r.available >= ?";
            $parameters[] = $minAvailability;
        }
        if ($nameQuery !== "") {
            $conditions[] = "r.name LIKE ?";
            $parameters[] = "%{$nameQuery}%";
        }
        $whereClause = empty($conditions) ? "" : "WHERE " . implode(" AND ", $conditions);
        $query = "SELECT rooms.uuid, rooms.name, rooms.description, rooms.category, rooms.price, rooms.available, room_commodities.type, images.url

        FROM rooms

        LEFT JOIN room_commodities ON rooms.uuid = room_commodities.room_uuid

        LEFT JOIN images ON rooms.uuid = images.room_uuid

        $whereClause

        ORDER BY rooms.category ASC, rooms.price ASC, rooms.available ASC";
        $result = mysqli_execute_query($connection, $query, $parameters);
        if (!$result instanceof mysqli_result) {
            throw new RuntimeException("No fue posible realizar la búsqueda de habitaciones.");
        }
        $rooms = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $uuid = $row["uuid"];
            if (!isset($rooms[$uuid])) {
                $rooms[$uuid] = [
                    "uuid" => $uuid,
                    "name" => $row["name"],
                    "description" => $row["description"],
                    "category" => $row["category"],
                    "price" => (float) $row["price"],
                    "available" => (int) $row["available"],
                    "commodities" => [],
                    "images" => []
                ];
            }
            if ($row["type"] !== null) {
                $rooms[$uuid]["commodities"][$row["type"]] = $row["type"];
            }
            if ($row["url"] !== null) {
                $rooms[$uuid]["images"][$row["url"]] = $row["url"];
            }
        }
        $roomObjects = [];
        foreach ($rooms as $room) {
            $roomObjects[] = new Room(
                $room["uuid"],
                $room["name"],
                $room["description"],
                $room["category"],
                $room["price"],
                $room["available"],
                array_values($room["commodities"]),
                array_values($room["images"])
            );
        }
        return $roomObjects;
    } catch (mysqli_sql_exception $exception) {
        throw new RuntimeException("Ocurrió un error al buscar habitaciones en la base de datos.", 0, $exception);
    } finally {
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        mysqli_close($connection);
    }
}

/**
 * Searches reservations by a substring of either the room name or the user name.
 *
 * The search term is applied to both names. Each match is returned as a
 * UserRoom entity containing both names, both UUIDs, and the reserved quantity.
 *
 * @param string $searchQuery Text to find in a room name or user name; an empty string matches all reservations.
 * @return array<int, UserRoom> Matching reservations, or an empty array if none match.
 * @throws RuntimeException If the search query fails.
 * @throws ValidationException If a retrieved reservation does not satisfy the UserRoom entity rules.
 */
function searchUserRooms(string $searchQuery): array
{
    $connection = connectDatabase();
    $result = null;
    try {
        $searchPattern = "%{$searchQuery}%";
        $query = "SELECT user_rooms.user_uuid, user_rooms.room_uuid, user_rooms.quantity,

            users.name AS user_name, rooms.name AS room_name

            FROM user_rooms

            JOIN users ON user_rooms.user_uuid = users.uuid

            JOIN rooms ON user_rooms.room_uuid = rooms.uuid

            WHERE rooms.name LIKE ? OR users.name LIKE ?";
        $result = mysqli_execute_query($connection, $query, [$searchPattern, $searchPattern]);
        if (!$result instanceof mysqli_result) {
            throw new RuntimeException("No fue posible buscar las habitaciones reservadas.");
        }
        $userRooms = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $userRooms[] = new UserRoom(
                $row["user_uuid"],
                $row["room_uuid"],
                (int) $row["quantity"],
                $row["user_name"],
                $row["room_name"]
            );
        }
        return $userRooms;
    } catch (mysqli_sql_exception $exception) {
        throw new RuntimeException("Ocurrió un error al buscar las habitaciones reservadas.", 0, $exception);
    } finally {
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        mysqli_close($connection);
    }
}

/**
 * Retrieves one room by UUID, including its commodities and image paths.
 *
 * The returned Room entity contains the room's scalar properties and the
 * associated strings from room_commodities and images. Returns null when no
 * room has the supplied UUID.
 *
 * @param string $uuid UUID of the room to retrieve.
 * @return Room|null The matching room, or null if it does not exist.
 * @throws RuntimeException If one of the room or association queries fails.
 * @throws ValidationException If stored room data does not satisfy the Room entity rules.
 */
function getRoomByUuid(string $uuid): ?Room
{
    $connection = connectDatabase();
    $roomResult = null;
    $commoditiesResult = null;
    $imagesResult = null;
    try {
        $roomResult = mysqli_execute_query($connection, "SELECT * FROM rooms WHERE uuid = ?", [$uuid]);
        if (!$roomResult instanceof mysqli_result) {
            throw new RuntimeException("No fue posible obtener la habitación.");
        }
        $roomData = mysqli_fetch_assoc($roomResult);
        if ($roomData === null) {
            return null;
        }
        $commoditiesResult = mysqli_execute_query($connection, "SELECT type FROM room_commodities WHERE room_uuid = ?", [$uuid]);
        if (!$commoditiesResult instanceof mysqli_result) {
            throw new RuntimeException("No fue posible obtener las comodidades de la habitación.");
        }
        $commodities = [];
        while ($row = mysqli_fetch_assoc($commoditiesResult)) {
            $commodities[] = $row["type"];
        }
        $imagesResult = mysqli_execute_query($connection, "SELECT url FROM images WHERE room_uuid = ?", [$uuid]);
        if (!$imagesResult instanceof mysqli_result) {
            throw new RuntimeException("No fue posible obtener las imágenes de la habitación.");
        }
        $images = [];
        while ($row = mysqli_fetch_assoc($imagesResult)) {
            $images[] = $row["url"];
        }
        return new Room(
            $roomData["uuid"],
            $roomData["name"],
            $roomData["description"],
            $roomData["category"],
            (float) $roomData["price"],
            (int) $roomData["available"],
            $commodities,
            $images
        );
    } catch (mysqli_sql_exception $exception) {
        throw new RuntimeException("Ocurrió un error al obtener la habitación de la base de datos.", 0, $exception);
    } finally {
        if ($roomResult instanceof mysqli_result) {
            mysqli_free_result($roomResult);
        }
        if ($commoditiesResult instanceof mysqli_result) {
            mysqli_free_result($commoditiesResult);
        }
        if ($imagesResult instanceof mysqli_result) {
            mysqli_free_result($imagesResult);
        }
        mysqli_close($connection);
    }
}

/**
 * Calculates the total inventory for one room type.
 *
 * The total is the current rooms.available value plus the sum of all quantities
 * reserved by users. This is different from the currently available quantity:
 * it includes rooms that are already reserved. Returns 0 if the UUID does not
 * identify an existing room.
 *
 * @param string $uuid UUID of the room type to count.
 * @return int Available plus reserved quantity, or 0 if the room does not exist.
 * @throws RuntimeException If the database query fails.
 */
function getTotalRooms(string $uuid): int
{
    $connection = connectDatabase();
    $result = null;
    try {
        $query = "SELECT rooms.available + COALESCE(SUM(user_rooms.quantity), 0) AS total

            FROM rooms

            LEFT JOIN user_rooms ON rooms.uuid = user_rooms.room_uuid

            WHERE rooms.uuid = ?

            GROUP BY rooms.uuid, rooms.available";
        $result = mysqli_execute_query($connection, $query, [$uuid]);
        if (!$result instanceof mysqli_result) {
            throw new RuntimeException("No fue posible obtener la cantidad total de habitaciones.");
        }
        $row = mysqli_fetch_assoc($result);
        return $row === null ? 0 : (int) $row["total"];
    } catch (mysqli_sql_exception $exception) {
        throw new RuntimeException("Ocurrió un error al obtener la cantidad total de habitaciones.", 0, $exception);
    } finally {
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        mysqli_close($connection);
    }
}

/**
 * Searches users by a substring of their name or email address.
 *
 * Matching users are returned as User entities. As with getAllUsers(), each
 * object contains the stored password hash; never expose or log that property.
 * Results are ordered by the role ENUM in descending declared order (admin
 * before regular in the current schema).
 *
 * @param string $searchQuery Text to find in a user's name or email; an empty string matches all users.
 * @return array<int, User> Matching users, or an empty array if none match.
 * @throws RuntimeException If the search query fails.
 * @throws ValidationException If a stored user record does not satisfy the User entity rules.
 */
function searchUsers(string $searchQuery): array
{
    $connection = connectDatabase();
    $result = null;
    try {
        $searchPattern = "%{$searchQuery}%";
        $result = mysqli_execute_query(
            $connection,
            "SELECT * FROM users

            WHERE name LIKE ? OR email LIKE ?

            ORDER BY role DESC",
            [$searchPattern, $searchPattern]
        );
        if (!$result instanceof mysqli_result) {
            throw new RuntimeException("No fue posible buscar los usuarios.");
        }
        $users = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = new User(
                $row["uuid"],
                $row["name"],
                $row["email"],
                $row["password"],
                $row["role"]
            );
        }
        return $users;
    } catch (mysqli_sql_exception $exception) {
        throw new RuntimeException("No se pudieron buscar los usuarios.", 0, $exception);
    } finally {
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        mysqli_close($connection);
    }
}

/**
 * Validates and registers a new regular user.
 *
 * Pass the original, plain-text password received by the server; this function
 * validates that it contains at least 12 characters, a special symbol, a number,
 * an uppercase letter, and a lowercase letter, then hashes it before storing
 * it. Do not hash the password before calling this function. The new user's
 * role is the database default, regular. The email address must be unique.
 *
 * @param string $name User's display name (maximum 48 characters).
 * @param string $email User's email address (maximum 96 characters and valid email syntax).
 * @param string $password Plain-text password to validate and hash before storage.
 * @return bool True when the user is inserted successfully.
 * @throws RuntimeException If validation, password hashing, or database insertion fails. A ValidationException may be available as the previous exception.
 */
function insertUser(string $name, string $email, string $password): bool
{
    try {
        InputValidator::validatePassword($password);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        if (!is_string($hashedPassword)) {
            throw new RuntimeException("No fue posible obfuscar la contraseña.");
        }
        $user = new User(null, $name, $email, $hashedPassword);
    } catch (ValidationException $exception) {
        throw new RuntimeException("Los datos del usuario no son válidos: {$exception->getMessage()}", 0, $exception);
    }
    $connection = connectDatabase();
    try {
        $inserted = mysqli_execute_query(
            $connection,
            "INSERT INTO users (name, email, password) VALUES (?, ?, ?)",
            [$user->getName(), $user->getEmail(), $user->getPassword()]
        );
        if (!$inserted) {
            throw new RuntimeException("No fue posible registrar el usuario.");
        }
        return true;
    } catch (mysqli_sql_exception $exception) {
        throw new RuntimeException("Ocurrió un error al registrar el usuario en la base de datos.", 0, $exception);
    } finally {
        mysqli_close($connection);
    }
}

/**
 * Creates a room and registers its commodity and image-path associations in one database transaction.
 *
 * The commodities array contains commodity type strings that must already
 * exist in the commodities table. The images array contains the complete set
 * of image paths to associate with the room; it does not contain uploaded file
 * data and this function does not save files to disk. Use saveRoomImages()
 * before calling this function when the form includes uploaded files, then pass
 * the returned paths here.
 *
 * IMAGE ORDER FOR INSERTION:
 * 1. Call saveRoomImages() and retain the returned paths.
 * 2. Pass those paths to insertRoom(). The room and its image records are
 *    committed together by this function.
 * 3. If this function fails, call deleteRoomImages() for the newly saved paths.
 *    The database transaction rolls back its records, but cannot remove files
 *    already saved in the filesystem. Do not delete those files before the
 *    insertion has failed.
 *
 * @param string $name Room name (maximum 255 characters; unique in the database).
 * @param string $description Room description (maximum 4095 characters).
 * @param string $category One of the allowed room categories: express, regular, comfort, or suite.
 * @param float $price Non-negative room price with at most two decimal places.
 * @param int $available Initial number of available units; must be zero or greater.
 * @param array<int, string> $commodities Commodity type strings already present in the commodities table.
 * @param array<int, string> $images Relative image paths returned by saveRoomImages(); defaults to no images.
 * @return bool True when the room and all associations are committed successfully.
 * @throws RuntimeException If entity validation or any part of the database transaction fails.
 */
function insertRoom(
    string $name,
    string $description,
    string $category,
    float $price,
    int $available,
    array $commodities = [],
    array $images = []
): bool {
    try {
        $room = new Room(null, $name, $description, $category, $price, $available, $commodities, $images);
    } catch (ValidationException $exception) {
        throw new RuntimeException("Los datos de la habitación no son válidos: {$exception->getMessage()}", 0, $exception);
    }
    $connection = connectDatabase();
    $transactionStarted = false;
    $roomResult = null;
    try {
        if (!mysqli_begin_transaction($connection)) {
            throw new RuntimeException("No fue posible iniciar el registro de la habitación.");
        }
        $transactionStarted = true;
        $inserted = mysqli_execute_query(
            $connection,
            "INSERT INTO rooms (name, description, category, price, available) VALUES (?, ?, ?, ?, ?)",
            [$room->getName(), $room->getDescription(), $room->getCategory(), $room->getPrice(), $room->getAvailable()]
        );
        if (!$inserted) {
            throw new RuntimeException("No fue posible registrar la habitación.");
        }
        $roomResult = mysqli_execute_query(
            $connection,
            "SELECT uuid FROM rooms WHERE name = ?",
            [$room->getName()]
        );
        if (!$roomResult instanceof mysqli_result) {
            throw new RuntimeException("No fue posible recuperar el identificador de la habitación.");
        }
        $roomData = mysqli_fetch_assoc($roomResult);
        if ($roomData === null) {
            throw new RuntimeException("No fue posible recuperar el identificador de la habitación.");
        }
        $roomUuid = $roomData["uuid"];
        mysqli_free_result($roomResult);
        $roomResult = null;
        foreach (array_unique($room->getCommodities()) as $commodity) {
            $inserted = mysqli_execute_query(
                $connection,
                "INSERT INTO room_commodities (room_uuid, type) VALUES (?, ?)",
                [$roomUuid, $commodity]
            );
            if (!$inserted) {
                throw new RuntimeException("No fue posible registrar las comodidades de la habitación.");
            }
        }
        foreach ($room->getImages() as $image) {
            $inserted = mysqli_execute_query(
                $connection,
                "INSERT INTO images (room_uuid, url) VALUES (?, ?)",
                [$roomUuid, $image]
            );
            if (!$inserted) {
                throw new RuntimeException("No fue posible registrar las imágenes de la habitación.");
            }
        }
        if (!mysqli_commit($connection)) {
            throw new RuntimeException("No fue posible completar el registro de la habitación.");
        }
        $transactionStarted = false;
        return true;
    } catch (mysqli_sql_exception $exception) {
        if ($transactionStarted) {
            mysqli_rollback($connection);
        }
        throw new RuntimeException("Ocurrió un error al registrar la habitación en la base de datos.", 0, $exception);
    } catch (Throwable $exception) {
        if ($transactionStarted) {
            mysqli_rollback($connection);
        }
        throw $exception;
    } finally {
        if ($roomResult instanceof mysqli_result) {
            mysqli_free_result($roomResult);
        }
        mysqli_close($connection);
    }
}

/**
 * Creates or increases a user's reservations for one or more room types.
 *
 * The rooms argument must be a non-empty associative array whose keys are
 * room UUIDs and whose values are positive integer quantities, for example
 * ["room-uuid" => 2]. For each room, the function checks current availability.
 * If the user already has a reservation for that room, its quantity is
 * increased; otherwise, a reservation is created. The corresponding available
 * inventory is decreased by the requested quantity.
 *
 * All requested room reservations are handled in one transaction. If a room
 * does not exist, any requested quantity is invalid, or any room has
 * insufficient availability, the whole operation is rolled back; no partial
 * reservation is kept.
 *
 * @param string $userUuid UUID of the user making the reservations.
 * @param array<string, int> $rooms Associative map of room UUIDs to positive quantities; must not be empty.
 * @return bool True when every reservation and availability change is committed.
 * @throws RuntimeException If validation fails, a room is missing, availability is insufficient, or the transaction fails.
 */
function insertUserRooms(string $userUuid, array $rooms): bool
{
    //$rooms is an associative array of the form rooms["uuid"] = quantity
    try {
        Validator::validateUuid("Identificador de usuario", $userUuid);
        if (empty($rooms)) {
            throw new RuntimeException("Debe seleccionar al menos una habitación.");
        }
        foreach ($rooms as $roomUuid => $quantity) {
            Validator::validateUuid("Identificador de habitación", $roomUuid);
            Validator::validateInteger("Cantidad", $quantity, 1);
        }
    } catch (ValidationException $exception) {
        throw new RuntimeException("Los datos de la compra no son válidos: {$exception->getMessage()}", 0, $exception);
    }
    $connection = connectDatabase();
    $transactionStarted = false;
    $result = null;
    try {
        if (!mysqli_begin_transaction($connection)) {
            throw new RuntimeException("No fue posible realizar la compra.");
        }
        $transactionStarted = true;
        foreach ($rooms as $roomUuid => $quantity) {
            // Lock the room on the database and check its availability.
            $result = mysqli_execute_query(
                $connection,
                "SELECT available FROM rooms WHERE uuid = ? FOR UPDATE",
                [$roomUuid]
            );
            if (!$result instanceof mysqli_result) {
                throw new RuntimeException("No fue posible consultar la disponibilidad de la habitación.");
            }
            $room = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            $result = null;
            if ($room === null) {
                throw new RuntimeException("Una de las habitaciones seleccionadas no existe.");
            }
            if ((int) $room["available"] < $quantity) {
                throw new RuntimeException("No hay suficientes unidades disponibles de una de las habitaciones seleccionadas.");
            }
            // Check whether the user already has a reservation for this room.
            $result = mysqli_execute_query(
                $connection,
                "SELECT quantity FROM user_rooms WHERE user_uuid = ? AND room_uuid = ? FOR UPDATE",
                [$userUuid, $roomUuid]
            );
            if (!$result instanceof mysqli_result) {
                throw new RuntimeException("No fue posible consultar las reservas existentes.");
            }
            $reservation = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            $result = null;
            if ($reservation !== null) {
                // Add the requested quantity to the existing reservation.
                $updated = mysqli_execute_query(
                    $connection,
                    "UPDATE user_rooms SET quantity = quantity + ? WHERE user_uuid = ? AND room_uuid = ?",
                    [$quantity, $userUuid, $roomUuid]
                );
                if (!$updated || mysqli_affected_rows($connection) !== 1) {
                    throw new RuntimeException("No fue posible actualizar la cantidad de habitaciones reservadas.");
                }
            } else {
                // Create a new reservation.
                $inserted = mysqli_execute_query(
                    $connection,
                    "INSERT INTO user_rooms (user_uuid, room_uuid, quantity) VALUES (?, ?, ?)",
                    [$userUuid, $roomUuid, $quantity]
                );
                if (!$inserted) {
                    throw new RuntimeException("No fue posible registrar una de las habitaciones.");
                }
            }
            // Subtract the purchased quantity from the available rooms.
            $updated = mysqli_execute_query(
                $connection,
                "UPDATE rooms SET available = available - ? WHERE uuid = ? AND available >= ?",
                [$quantity, $roomUuid, $quantity]
            );
            if (!$updated || mysqli_affected_rows($connection) !== 1) {
                throw new RuntimeException("No fue posible actualizar la disponibilidad de una de las habitaciones.");
            }
        }
        if (!mysqli_commit($connection)) {
            throw new RuntimeException("No fue posible completar la compra.");
        }
        $transactionStarted = false;
        return true;
    } catch (mysqli_sql_exception $exception) {
        if ($transactionStarted) {
            mysqli_rollback($connection);
        }
        throw new RuntimeException("Ocurrió un error al registrar la compra en la base de datos.", 0, $exception);
    } catch (Throwable $exception) {
        if ($transactionStarted) {
            mysqli_rollback($connection);
        }
        throw $exception;
    } finally {
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        mysqli_close($connection);
    }
}

/**
 * Partially updates a room and, when supplied, replaces its commodity and image-path associations.
 *
 * Every argument other than the UUID is optional. A null value means that the
 * corresponding property is left unchanged. At least one non-null update
 * value must be provided. For commodities and images, a supplied array is the
 * complete replacement list, not a list of additions: existing associations
 * are removed and the supplied values are inserted. Supplying an empty array
 * clears all associations of that type.
 *
 * The images array must contain the complete final set of paths to retain for
 * the room, including existing paths that should remain and any new paths
 * returned by saveRoomImages(). It must not contain the uploaded files array.
 * This function changes database records only; it does not save or delete
 * physical image files.
 *
 * IMAGE ORDER FOR UPDATES:
 * 1. Retrieve the current Room (for example, with getRoomByUuid()) and keep
 *    its existing image paths. Determine which existing paths will be removed.
 * 2. Save any newly uploaded files with saveRoomImages().
 * 3. Build the complete final image-path array from retained old paths plus
 *    the newly saved paths, and pass that array as $images to updateRoom().
 * 4. If updateRoom() fails, call deleteRoomImages() only for the newly saved
 *    paths. The database transaction rolls back, so the old image records and
 *    files must remain untouched.
 * 5. After updateRoom() succeeds and commits, call deleteRoomImages() for the
 *    old paths that were removed. Never delete removed files before the commit.
 *
 * If $images is null or omitted, the current image associations are preserved
 * and no image-file deletion is required. If $images is [], all image records
 * are removed from the room; delete the corresponding physical files only
 * after this function succeeds. The same complete-replacement rule applies to
 * the commodities array, but commodity file handling is not applicable.
 *
 * @param string $uuid UUID of the room to update.
 * @param string|null $name New room name, or null to preserve the current name.
 * @param string|null $description New description, or null to preserve the current description.
 * @param string|null $category New allowed room category, or null to preserve the current category.
 * @param int|float|null $price New non-negative price with at most two decimals, or null to preserve the current price.
 * @param int|null $available New non-negative available quantity, or null to preserve the current quantity.
 * @param array<int, string>|null $commodities Complete replacement array of commodity type strings, null to preserve, or [] to clear.
 * @param array<int, string>|null $images Complete replacement array of image paths, null to preserve, or [] to clear.
 * @param array<int, string> $additionalImages Uploaded image paths to append under the room lock.
 * @return bool True when the database update is committed successfully.
 * @throws RuntimeException If no update value is provided, the room does not exist, validation fails, or the transaction fails.
 */
function updateRoom(
    string $uuid,
    mixed $name = null,
    mixed $description = null,
    mixed $category = null,
    mixed $price = null,
    mixed $available = null,
    mixed $commodities = null,
    mixed $images = null,
    array $additionalImages = []
): bool {
    try {
        Validator::validateUuid("Identificador de habitación", $uuid);
        if ($name === null && $description === null && $category === null && $price === null && $available === null && $commodities === null && $images === null && $additionalImages === []) {
            throw new RuntimeException("Debe proporcionar al menos un dato para actualizar.");
        }
    } catch (ValidationException $exception) {
        throw new RuntimeException("Los datos de la habitación no son válidos: {$exception->getMessage()}", 0, $exception);
    }
    $connection = connectDatabase();
    $transactionStarted = false;
    $roomResult = null;
    $commoditiesResult = null;
    $imagesResult = null;
    try {
        if (!mysqli_begin_transaction($connection)) {
            throw new RuntimeException("No fue posible iniciar la actualización de la habitación.");
        }
        $transactionStarted = true;
        // Lock the room before retrieving its current values.
        $roomResult = mysqli_execute_query(
            $connection,
            "SELECT * FROM rooms WHERE uuid = ? FOR UPDATE",
            [$uuid]
        );
        if (!$roomResult instanceof mysqli_result) {
            throw new RuntimeException("No fue posible consultar la habitación.");
        }
        $roomData = mysqli_fetch_assoc($roomResult);
        mysqli_free_result($roomResult);
        $roomResult = null;
        if ($roomData === null) {
            throw new DomainException("La habitación seleccionada no existe.");
        }
        // Retrieve the current commodities.
        $commoditiesResult = mysqli_execute_query(
            $connection,
            "SELECT type FROM room_commodities WHERE room_uuid = ?",
            [$uuid]
        );
        if (!$commoditiesResult instanceof mysqli_result) {
            throw new RuntimeException("No fue posible obtener las comodidades de la habitación.");
        }
        $currentCommodities = [];
        while ($row = mysqli_fetch_assoc($commoditiesResult)) {
            $currentCommodities[] = $row["type"];
        }
        mysqli_free_result($commoditiesResult);
        $commoditiesResult = null;
        // Retrieve the current images.
        $imagesResult = mysqli_execute_query(
            $connection,
            "SELECT url FROM images WHERE room_uuid = ?",
            [$uuid]
        );
        if (!$imagesResult instanceof mysqli_result) {
            throw new RuntimeException("No fue posible obtener las imágenes de la habitación.");
        }
        $currentImages = [];
        while ($row = mysqli_fetch_assoc($imagesResult)) {
            $currentImages[] = $row["url"];
        }
        mysqli_free_result($imagesResult);
        $imagesResult = null;
        // Preserve values whose arguments were not provided.
        $replaceImages = $images !== null || $additionalImages !== [];
        $replaceCommodities = $commodities !== null;
        $commodities ??= $currentCommodities;
        $images = array_merge($images ?? $currentImages, $additionalImages);
        try {
            $room = new Room(
                $uuid,
                $name ?? $roomData["name"],
                $description ?? $roomData["description"],
                $category ?? $roomData["category"],
                $price ?? (float) $roomData["price"],
                $available ?? (int) $roomData["available"],
                $commodities,
                $images
            );
        } catch (ValidationException $exception) {
            throw new RuntimeException("Los datos de la habitación no son válidos: {$exception->getMessage()}", 0, $exception);
        }
        // Update the room's main properties.
        $updated = mysqli_execute_query(
            $connection,
            "UPDATE rooms

            SET name = ?, description = ?, category = ?, price = ?, available = ?

            WHERE uuid = ?",
            [
                $room->getName(),
                $room->getDescription(),
                $room->getCategory(),
                $room->getPrice(),
                $room->getAvailable(),
                $room->getUuid()
            ]
        );
        if (!$updated) {
            throw new RuntimeException("No fue posible actualizar los datos de la habitación.");
        }
        // Replace commodities only if a new array was provided.
        if ($replaceCommodities) {
            $deleted = mysqli_execute_query(
                $connection,
                "DELETE FROM room_commodities WHERE room_uuid = ?",
                [$room->getUuid()]
            );
            if (!$deleted) {
                throw new RuntimeException("No fue posible reemplazar las comodidades de la habitación.");
            }
            foreach (array_unique($room->getCommodities()) as $commodity) {
                $inserted = mysqli_execute_query(
                    $connection,
                    "INSERT INTO room_commodities (room_uuid, type) VALUES (?, ?)",
                    [$room->getUuid(), $commodity]
                );
                if (!$inserted) {
                    throw new RuntimeException("No fue posible registrar una de las comodidades de la habitación.");
                }
            }
        }
        // Replace images only if a new array was provided.
        if ($replaceImages) {
            $deleted = mysqli_execute_query(
                $connection,
                "DELETE FROM images WHERE room_uuid = ?",
                [$room->getUuid()]
            );
            if (!$deleted) {
                throw new RuntimeException("No fue posible reemplazar las imágenes de la habitación.");
            }
            foreach ($room->getImages() as $image) {
                $inserted = mysqli_execute_query(
                    $connection,
                    "INSERT INTO images (room_uuid, url) VALUES (?, ?)",
                    [$room->getUuid(), $image]
                );
                if (!$inserted) {
                    throw new RuntimeException("No fue posible registrar una de las imágenes de la habitación.");
                }
            }
        }
        if (!mysqli_commit($connection)) {
            throw new RuntimeException("No fue posible completar la actualización de la habitación.");
        }
        $transactionStarted = false;
        return true;
    } catch (mysqli_sql_exception $exception) {
        if ($transactionStarted) {
            mysqli_rollback($connection);
        }
        throw new RuntimeException("Ocurrió un error al actualizar la habitación en la base de datos.", 0, $exception);
    } catch (Throwable $exception) {
        if ($transactionStarted) {
            mysqli_rollback($connection);
        }
        throw $exception;
    } finally {
        if ($roomResult instanceof mysqli_result) {
            mysqli_free_result($roomResult);
        }
        if ($commoditiesResult instanceof mysqli_result) {
            mysqli_free_result($commoditiesResult);
        }
        if ($imagesResult instanceof mysqli_result) {
            mysqli_free_result($imagesResult);
        }
        mysqli_close($connection);
    }
}

/**
 * Decreases a user's reservation for a room type by exactly one unit.
 *
 * If the reservation quantity is one, the reservation row is deleted. If it
 * is greater than one, the quantity is reduced by one. In both cases, one unit
 * is returned to the room's available inventory. The reservation change and
 * inventory adjustment are committed together in one transaction.
 *
 * @param string $userUuid UUID of the user whose reservation is being reduced.
 * @param string $roomUuid UUID of the room type in the reservation.
 * @return bool True when one reserved unit is removed and returned to inventory.
 * @throws RuntimeException If either UUID is invalid, the room or reservation does not exist, or the transaction fails.
 */
function decreaseUserRoom(string $userUuid, string $roomUuid): bool
{
    try {
        Validator::validateUuid("Identificador de usuario", $userUuid);
        Validator::validateUuid("Identificador de habitación", $roomUuid);
    } catch (ValidationException $exception) {
        throw new RuntimeException("Los identificadores proporcionados no son válidos: {$exception->getMessage()}", 0, $exception);
    }
    $connection = connectDatabase();
    $transactionStarted = false;
    $roomResult = null;
    $reservationResult = null;
    try {
        if (!mysqli_begin_transaction($connection)) {
            throw new RuntimeException("No fue posible iniciar la actualización de la reservación.");
        }
        $transactionStarted = true;
        // Lock the room before accessing its reservation.
        $roomResult = mysqli_execute_query(
            $connection,
            "SELECT uuid FROM rooms WHERE uuid = ? FOR UPDATE",
            [$roomUuid]
        );
        if (!$roomResult instanceof mysqli_result) {
            throw new RuntimeException("No fue posible consultar la habitación.");
        }
        $roomData = mysqli_fetch_assoc($roomResult);
        mysqli_free_result($roomResult);
        $roomResult = null;
        if ($roomData === null) {
            throw new DomainException("La habitación seleccionada no existe.");
        }
        // Lock and retrieve the reservation.
        $reservationResult = mysqli_execute_query(
            $connection,
            "SELECT quantity FROM user_rooms WHERE user_uuid = ? AND room_uuid = ? FOR UPDATE",
            [$userUuid, $roomUuid]
        );
        if (!$reservationResult instanceof mysqli_result) {
            throw new RuntimeException("No fue posible consultar la reservación.");
        }
        $reservationData = mysqli_fetch_assoc($reservationResult);
        mysqli_free_result($reservationResult);
        $reservationResult = null;
        if ($reservationData === null) {
            throw new DomainException("No existe una reservación para esta habitación y usuario.");
        }
        $quantity = (int) $reservationData["quantity"];
        // Remove the reservation if only one room remains.
        if ($quantity === 1) {
            $updated = mysqli_execute_query(
                $connection,
                "DELETE FROM user_rooms WHERE user_uuid = ? AND room_uuid = ?",
                [$userUuid, $roomUuid]
            );
        } else {
            $updated = mysqli_execute_query(
                $connection,
                "UPDATE user_rooms SET quantity = quantity - 1 WHERE user_uuid = ? AND room_uuid = ?",
                [$userUuid, $roomUuid]
            );
        }
        if (!$updated) {
            throw new RuntimeException("No fue posible actualizar la cantidad de habitaciones reservadas.");
        }
        // Return one room to the available inventory.
        $updated = mysqli_execute_query(
            $connection,
            "UPDATE rooms SET available = available + 1 WHERE uuid = ?",
            [$roomUuid]
        );
        if (!$updated) {
            throw new RuntimeException("No fue posible actualizar la disponibilidad de la habitación.");
        }
        if (!mysqli_commit($connection)) {
            throw new RuntimeException("No fue posible completar la actualización de la reservación.");
        }
        $transactionStarted = false;
        return true;
    } catch (mysqli_sql_exception $exception) {
        if ($transactionStarted) {
            mysqli_rollback($connection);
        }
        throw new RuntimeException("Ocurrió un error al actualizar la reservación en la base de datos.", 0, $exception);
    } catch (Throwable $exception) {
        if ($transactionStarted) {
            mysqli_rollback($connection);
        }
        throw $exception;
    } finally {
        if ($roomResult instanceof mysqli_result) {
            mysqli_free_result($roomResult);
        }
        if ($reservationResult instanceof mysqli_result) {
            mysqli_free_result($reservationResult);
        }
        mysqli_close($connection);
    }
}

/**
 * Removes a user's entire reservation for one room type.
 *
 * The reservation row is deleted and its full reserved quantity is returned
 * to the room's available inventory. Both changes occur in one transaction.
 * Use decreaseUserRoom() instead when only one unit should be removed.
 *
 * @param string $userUuid UUID of the user whose reservation is being removed.
 * @param string $roomUuid UUID of the room type in the reservation.
 * @return bool True when the full reservation is removed and its quantity is returned to inventory.
 * @throws RuntimeException If either UUID is invalid, the room or reservation does not exist, or the transaction fails.
 */
function removeUserRoom(string $userUuid, string $roomUuid): bool
{
    try {
        Validator::validateUuid("Identificador de usuario", $userUuid);
        Validator::validateUuid("Identificador de habitación", $roomUuid);
    } catch (ValidationException $exception) {
        throw new RuntimeException("Los identificadores proporcionados no son válidos: {$exception->getMessage()}", 0, $exception);
    }
    $connection = connectDatabase();
    $transactionStarted = false;
    $roomResult = null;
    $reservationResult = null;
    try {
        if (!mysqli_begin_transaction($connection)) {
            throw new RuntimeException("No fue posible iniciar la transacción para eliminar la reservación.");
        }
        $transactionStarted = true;
        // Lock the room before accessing its reservation.
        $roomResult = mysqli_execute_query(
            $connection,
            "SELECT uuid FROM rooms WHERE uuid = ? FOR UPDATE",
            [$roomUuid]
        );
        if (!$roomResult instanceof mysqli_result) {
            throw new RuntimeException("No fue posible consultar la habitación.");
        }
        $roomData = mysqli_fetch_assoc($roomResult);
        mysqli_free_result($roomResult);
        $roomResult = null;
        if ($roomData === null) {
            throw new DomainException("La habitación seleccionada no existe.");
        }
        // Lock and retrieve the reservation quantity.
        $reservationResult = mysqli_execute_query(
            $connection,
            "SELECT quantity FROM user_rooms WHERE user_uuid = ? AND room_uuid = ? FOR UPDATE",
            [$userUuid, $roomUuid]
        );
        if (!$reservationResult instanceof mysqli_result) {
            throw new RuntimeException("No fue posible consultar la reservación.");
        }
        $reservationData = mysqli_fetch_assoc($reservationResult);
        mysqli_free_result($reservationResult);
        $reservationResult = null;
        if ($reservationData === null) {
            throw new DomainException("No existe una reservación para esta habitación y usuario.");
        }
        $quantity = (int) $reservationData["quantity"];
        // Remove the entire reservation.
        $deleted = mysqli_execute_query(
            $connection,
            "DELETE FROM user_rooms WHERE user_uuid = ? AND room_uuid = ?",
            [$userUuid, $roomUuid]
        );
        if (!$deleted) {
            throw new RuntimeException("No fue posible eliminar la reservación.");
        }
        // Return all reserved rooms to the available inventory.
        $updated = mysqli_execute_query(
            $connection,
            "UPDATE rooms SET available = available + ? WHERE uuid = ?",
            [$quantity, $roomUuid]
        );
        if (!$updated) {
            throw new RuntimeException("No fue posible actualizar la disponibilidad de la habitación.");
        }
        if (!mysqli_commit($connection)) {
            throw new RuntimeException("No fue posible completar la transacción para eliminar la reservación.");
        }
        $transactionStarted = false;
        return true;
    } catch (mysqli_sql_exception $exception) {
        if ($transactionStarted) {
            mysqli_rollback($connection);
        }
        throw new RuntimeException("Ocurrió un error al eliminar la reservación de la base de datos.", 0, $exception);
    } catch (Throwable $exception) {
        if ($transactionStarted) {
            mysqli_rollback($connection);
        }
        throw $exception;
    } finally {
        if ($roomResult instanceof mysqli_result) {
            mysqli_free_result($roomResult);
        }
        if ($reservationResult instanceof mysqli_result) {
            mysqli_free_result($reservationResult);
        }
        mysqli_close($connection);
    }
}

/**
 * Switches a user's role between regular and admin.
 *
 * The current role is read and changed within one transaction. A regular user
 * becomes an admin, and an admin becomes a regular user. The operation fails
 * if the UUID does not identify an existing user.
 *
 * @param string $userUuid UUID of the user whose role should be toggled.
 * @param string|null $desiredRole Explicit role from a confirmation form, or null to toggle.
 * @return bool True when the role change is committed successfully.
 * @throws RuntimeException If the UUID is invalid, the user does not exist, or the transaction fails.
 */
function toggleUserRole(string $userUuid, ?string $desiredRole = null): bool
{
    try {
        Validator::validateUuid("Identificador de usuario", $userUuid);
    } catch (ValidationException $exception) {
        throw new RuntimeException("El identificador de usuario no es válido: {$exception->getMessage()}", 0, $exception);
    }
    $connection = connectDatabase();
    $transactionStarted = false;
    $userResult = null;
    try {
        if (!mysqli_begin_transaction($connection)) {
            throw new RuntimeException("No fue posible iniciar la actualización del rol.");
        }
        $transactionStarted = true;
        // Lock the user before retrieving the current role.
        $userResult = mysqli_execute_query(
            $connection,
            "SELECT role FROM users WHERE uuid = ? FOR UPDATE",
            [$userUuid]
        );
        if (!$userResult instanceof mysqli_result) {
            throw new RuntimeException("No fue posible consultar el usuario.");
        }
        $userData = mysqli_fetch_assoc($userResult);
        mysqli_free_result($userResult);
        $userResult = null;
        if ($userData === null) {
            throw new DomainException("El usuario seleccionado no existe.");
        }
        $newRole = $desiredRole ?? ($userData["role"] === "regular" ? "admin" : "regular");
        Validator::validateEnum("Rol", $newRole, ["regular", "admin"]);
        $updated = mysqli_execute_query(
            $connection,
            "UPDATE users SET role = ? WHERE uuid = ?",
            [$newRole, $userUuid]
        );
        if (!$updated) {
            throw new RuntimeException("No fue posible actualizar el rol del usuario.");
        }
        if (!mysqli_commit($connection)) {
            throw new RuntimeException("No fue posible completar la actualización del rol.");
        }
        $transactionStarted = false;
        return true;
    } catch (mysqli_sql_exception $exception) {
        if ($transactionStarted) {
            mysqli_rollback($connection);
        }
        throw new RuntimeException("Ocurrió un error al actualizar el rol del usuario en la base de datos.", 0, $exception);
    } catch (Throwable $exception) {
        if ($transactionStarted) {
            mysqli_rollback($connection);
        }
        throw $exception;
    } finally {
        if ($userResult instanceof mysqli_result) {
            mysqli_free_result($userResult);
        }
        mysqli_close($connection);
    }
}

/**
 * Deletes a room and its database associations.
 *
 * The foreign-key rules in the current schema cascade this deletion to the
 * room_commodities, user_rooms, and images records associated with the room.
 * This function deletes database records only; it does not delete the physical
 * image files stored in room_images/.
 *
 * IMAGE ORDER FOR ROOM DELETION:
 * 1. Retrieve the room before deletion (for example, with getRoomByUuid())
 *    and retain the image paths from Room::getImages().
 * 2. Call deleteRoom().
 * 3. Only after deleteRoom() succeeds, call deleteRoomImages() with the saved
 *    paths. If the database deletion fails, leave all image files untouched.
 *
 * @param string $uuid UUID of the room to delete.
 * @return bool True when the room record is deleted successfully.
 * @throws RuntimeException If the UUID is invalid, the room does not exist, or the database deletion fails.
 */
function deleteRoom(string $uuid): bool
{
    try {
        Validator::validateUuid("Identificador de habitación", $uuid);
    } catch (ValidationException $exception) {
        throw new RuntimeException("El identificador de habitación no es válido: {$exception->getMessage()}", 0, $exception);
    }
    $connection = connectDatabase();
    try {
        $deleted = mysqli_execute_query(
            $connection,
            "DELETE FROM rooms WHERE uuid = ?",
            [$uuid]
        );
        if (!$deleted) {
            throw new RuntimeException("No fue posible eliminar la habitación.");
        }
        if (mysqli_affected_rows($connection) === 0) {
            throw new DomainException("La habitación seleccionada no existe.");
        }
        return true;
    } catch (mysqli_sql_exception $exception) {
        throw new RuntimeException("Ocurrió un error al eliminar la habitación de la base de datos.", 0, $exception);
    } finally {
        mysqli_close($connection);
    }
}

/**
 * Retrieves several rooms and their associated commodities and image paths in a batch.
 *
 * Duplicate UUIDs are removed. The returned Room entities follow the order of
 * the UUIDs in the input array; UUIDs that do not identify an existing room
 * are skipped. Each Room contains its associated commodity strings and image
 * paths. An empty input array returns an empty result without querying the DB.
 *
 * @param array<int, string> $uuids UUIDs of the rooms to retrieve.
 * @return array<int, Room> Existing rooms in the same order as their first occurrence in the input array.
 * @throws RuntimeException If any UUID fails validation or any database query fails.
 * @throws ValidationException If retrieved room data does not satisfy the Room entity rules.
 */
function getRoomsByUuids(array $uuids): array
{
    try {
        foreach ($uuids as $uuid) {
            Validator::validateUuid("Identificador de habitación", $uuid);
        }
    } catch (ValidationException $exception) {
        throw new RuntimeException("Los identificadores de habitación no son válidos: {$exception->getMessage()}", 0, $exception);
    }
    $uuids = array_values(array_unique($uuids));
    if (empty($uuids)) {
        return [];
    }
    $connection = connectDatabase();
    $transactionStarted = false;
    $roomsResult = null;
    $commoditiesResult = null;
    $imagesResult = null;
    try {
        if (!mysqli_begin_transaction($connection)) {
            throw new RuntimeException("No fue posible iniciar la consulta de habitaciones.");
        }
        $transactionStarted = true;
        $placeholders = implode(", ", array_fill(0, count($uuids), "?"));
        // Retrieve all requested rooms.
        $roomsResult = mysqli_execute_query(
            $connection,
            "SELECT * FROM rooms WHERE uuid IN ({$placeholders})",
            $uuids
        );
        if (!$roomsResult instanceof mysqli_result) {
            throw new RuntimeException("No fue posible obtener las habitaciones.");
        }
        $roomsByUuid = [];
        $commoditiesByRoom = [];
        $imagesByRoom = [];
        while ($row = mysqli_fetch_assoc($roomsResult)) {
            $uuid = $row["uuid"];
            $roomsByUuid[$uuid] = $row;
            $commoditiesByRoom[$uuid] = [];
            $imagesByRoom[$uuid] = [];
        }
        mysqli_free_result($roomsResult);
        $roomsResult = null;
        // Stop if none of the requested rooms exists.
        if (empty($roomsByUuid)) {
            if (!mysqli_commit($connection)) {
                throw new RuntimeException("No fue posible completar la consulta de habitaciones.");
            }
            $transactionStarted = false;
            return [];
        }
        $roomUuids = array_keys($roomsByUuid);
        $placeholders = implode(", ", array_fill(0, count($roomUuids), "?"));
        // Retrieve all associated commodities.
        $commoditiesResult = mysqli_execute_query(
            $connection,
            "SELECT room_uuid, type FROM room_commodities WHERE room_uuid IN ($placeholders)",
            $roomUuids
        );
        if (!$commoditiesResult instanceof mysqli_result) {
            throw new RuntimeException("No fue posible obtener las comodidades de las habitaciones.");
        }
        while ($row = mysqli_fetch_assoc($commoditiesResult)) {
            $commoditiesByRoom[$row["room_uuid"]][] = $row["type"];
        }
        mysqli_free_result($commoditiesResult);
        $commoditiesResult = null;
        // Retrieve all associated images.
        $imagesResult = mysqli_execute_query(
            $connection,
            "SELECT room_uuid, url FROM images WHERE room_uuid IN ($placeholders)",
            $roomUuids
        );
        if (!$imagesResult instanceof mysqli_result) {
            throw new RuntimeException("No fue posible obtener las imágenes de las habitaciones.");
        }
        while ($row = mysqli_fetch_assoc($imagesResult)) {
            $imagesByRoom[$row["room_uuid"]][] = $row["url"];
        }
        mysqli_free_result($imagesResult);
        $imagesResult = null;
        // Construct Room objects in the original UUID order.
        $rooms = [];
        foreach ($uuids as $uuid) {
            if (!isset($roomsByUuid[$uuid])) {
                continue;
            }
            $row = $roomsByUuid[$uuid];
            $rooms[] = new Room(
                $uuid,
                $row["name"],
                $row["description"],
                $row["category"],
                (float) $row["price"],
                (int) $row["available"],
                $commoditiesByRoom[$uuid],
                $imagesByRoom[$uuid]
            );
        }
        if (!mysqli_commit($connection)) {
            throw new RuntimeException("No fue posible completar la consulta de habitaciones.");
        }
        $transactionStarted = false;
        return $rooms;
    } catch (mysqli_sql_exception $exception) {
        if ($transactionStarted) {
            mysqli_rollback($connection);
        }
        throw new RuntimeException("Ocurrió un error al obtener las habitaciones de la base de datos.", 0, $exception);
    } catch (Throwable $exception) {
        if ($transactionStarted) {
            mysqli_rollback($connection);
        }
        throw $exception;
    } finally {
        if ($roomsResult instanceof mysqli_result) {
            mysqli_free_result($roomsResult);
        }
        if ($commoditiesResult instanceof mysqli_result) {
            mysqli_free_result($commoditiesResult);
        }
        if ($imagesResult instanceof mysqli_result) {
            mysqli_free_result($imagesResult);
        }
        mysqli_close($connection);
    }
}

/**
 * Authenticates a user with an email address and plain-text password.
 *
 * The password is checked against the stored password hash using
 * password_verify(). On success, the user's User entity is returned. The
 * entity's password property contains the stored hash and must never be
 * exposed to the browser or included in logs. Invalid credentials produce
 * the same error message whether the email or password is incorrect.
 *
 * @param string $email Email address submitted for sign-in.
 * @param string $password Plain-text password submitted for sign-in; do not hash it before calling.
 * @return User|null The authenticated user. The current implementation returns a User on success and throws for invalid credentials; it does not currently return null.
 * @throws InvalidUuidException If the email/password pair is not recognized (the exception is currently used for the generic sign-in error).
 * @throws RuntimeException If the database query fails.
 */
function getUserWithEmailAndPassword(string $email, string $password): ?User
{
    $connection = connectDatabase();
    $result = null;
    try {
        $result = mysqli_execute_query(
            $connection,
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );
        if (!$result instanceof mysqli_result) {
            throw new RuntimeException("No fue posible consultar los datos del usuario.");
        }
        $row = mysqli_fetch_assoc($result);
        if ($row === null || !password_verify($password, $row["password"])) {
            throw new InvalidUuidException("Inicio de Sesión", "El correo o contraseña son incorrectos.");
        }
        return new User(
            $row["uuid"],
            $row["name"],
            $row["email"],
            $row["password"],
            $row["role"]
        );
    } catch (mysqli_sql_exception $exception) {
        throw new RuntimeException("Ocurrió un error al verificar los datos del usuario.", 0, $exception);
    } finally {
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        mysqli_close($connection);
    }
}

/**
 * Validates and saves the uploaded files for a room, returning their relative paths.
 *
 * Pass the multi-file upload array produced by PHP for an input such as
 * name="images[]" with the multiple attribute (normally $_FILES["images"]).
 * An empty array, or a submission in which every entry has UPLOAD_ERR_NO_FILE,
 * returns [] without creating the destination directory. Each selected file
 * is validated by InputValidator::validateImage(): it must be a genuine PHP
 * upload, no larger than 8 MB, and a valid JPEG, PNG, GIF, or WebP image.
 *
 * Files receive random names; their extension is determined from the detected
 * MIME type, not from the client-provided filename. All selected images are
 * validated before any are moved. If moving one file fails, the files already
 * moved during that call are removed before the error is rethrown.
 *
 * The destination directory must be the filesystem path to the project's
 * room_images/ directory. Returned values always use the relative prefix
 * "room_images/", so the destination must correspond to that directory for
 * the returned paths to resolve correctly when stored in images.url.
 *
 * IMAGE ORDER FOR INSERTION AND UPDATE:
 * Call this function before insertRoom() or updateRoom(). Pass its returned
 * paths as the database image list. If the subsequent database operation fails,
 * call deleteRoomImages() for these newly saved paths. For updates, delete
 * removed old paths only after updateRoom() has committed successfully.
 *
 * @param array<string, array<int, mixed>> $images PHP's multi-file upload structure, usually $_FILES["images"].
 * @param string $destinationDirectory Absolute filesystem path to the project's room_images/ directory.
 * @return array<int, string> Relative paths such as "room_images/<random-name>.jpg"; [] when no files were selected.
 * @throws InvalidImageException If an uploaded file is missing required upload data, is too large, or has an unsupported or invalid image format.
 * @throws RuntimeException If the destination directory cannot be created or a file cannot be saved.
 */
function saveRoomImages(array $images, string $destinationDirectory): array
{
    if ($images === []) {
        return [];
    }
    foreach (["name", "tmp_name", "error", "size"] as $key) {
        if (!isset($images[$key]) || !is_array($images[$key])) {
            throw new InvalidImageException("Imágenes", "La información de los archivos está incompleta.");
        }
    }
    $validImages = [];
    foreach ($images["name"] as $index => $name) {
        if (!array_key_exists($index, $images["tmp_name"]) || !array_key_exists($index, $images["error"]) || !array_key_exists($index, $images["size"])) {
            throw new InvalidImageException("Imágenes", "La información de los archivos está incompleta.");
        }
        $image = [
            "name" => $name,
            "tmp_name" => $images["tmp_name"][$index],
            "error" => $images["error"][$index],
            "size" => $images["size"][$index]
        ];
        if ($image["error"] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $mimeType = InputValidator::validateImage($image);
        $validImages[] = [
            "tmp_name" => $image["tmp_name"],
            "mime_type" => $mimeType
        ];
    }
    if ($validImages === []) {
        return [];
    }
    if (!is_dir($destinationDirectory) && !mkdir($destinationDirectory, 0755, true) && !is_dir($destinationDirectory)) {
        throw new RuntimeException("No fue posible crear la carpeta de imágenes.");
    }
    $extensions = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/gif" => "gif",
        "image/webp" => "webp"
    ];
    $savedPaths = [];
    $savedFiles = [];
    try {
        foreach ($validImages as $image) {
            $fileName = bin2hex(random_bytes(16)) . "." . $extensions[$image["mime_type"]];
            $destinationPath = rtrim($destinationDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
            if (!move_uploaded_file($image["tmp_name"], $destinationPath)) {
                throw new RuntimeException("No fue posible guardar una de las imágenes.");
            }
            $savedFiles[] = $destinationPath;
            $savedPaths[] = "room_images/$fileName";
        }
    } catch (Throwable $exception) {
        foreach ($savedFiles as $savedFile) {
            if (is_file($savedFile)) {
                unlink($savedFile);
            }
        }
        throw $exception;
    }
    return $savedPaths;
}

/**
 * Deletes physical room image files using their stored relative paths.
 *
 * Pass only image paths obtained from saveRoomImages(), Room::getImages(),
 * or trusted image records belonging to the room being processed. Do not pass
 * arbitrary paths submitted by a browser. The projectDirectory argument must
 * identify the project root containing room_images/.
 *
 * An empty array performs no operation. Paths must use the "room_images/"
 * prefix and identify a filename directly inside that directory. Files that
 * are already missing are ignored. If a file cannot be deleted, the function
 * throws at that point; any earlier files in the same call may already have
 * been deleted, because filesystem deletion is not transactional.
 *
 * IMPORTANT TRANSACTION ORDER:
 * - Failed room insertion: delete the newly saved files after insertRoom()
 *   fails and its database transaction has rolled back.
 * - Room update: if updateRoom() fails, delete only the newly saved files; if
 *   it succeeds, delete the old paths that were removed from the final image
 *   list. Never delete old files before the update commits.
 * - Room deletion: capture the room's image paths before deleteRoom(), then
 *   delete those files only after deleteRoom() succeeds and its database
 *   deletion has completed. The database cascade removes image records, not
 *   the files in room_images/.
 *
 * @param array<int, string> $imagePaths Relative room image paths to delete, normally obtained from the database or saveRoomImages().
 * @param string $projectDirectory Absolute path to the project root containing room_images/.
 * @return void
 * @throws RuntimeException If the project directory cannot be resolved, a path is invalid, or a file cannot be deleted.
 */
function deleteRoomImages(array $imagePaths, string $projectDirectory): void
{
    if ($imagePaths === []) {
        return;
    }
    $projectRoot = realpath($projectDirectory);
    if ($projectRoot === false) {
        throw new RuntimeException("No fue posible localizar la carpeta del proyecto.");
    }
    $roomImagesDirectory = $projectRoot . DIRECTORY_SEPARATOR . "room_images";
    if (!is_dir($roomImagesDirectory)) {
        return;
    }
    foreach ($imagePaths as $imagePath) {
        if (!is_string($imagePath) || !str_starts_with($imagePath, "room_images/")) {
            throw new RuntimeException("La ruta de una imagen no es válida.");
        }
        $fileName = substr($imagePath, strlen("room_images/"));
        if ($fileName === "" || basename($fileName) !== $fileName || str_contains($fileName, "\\\\")) {
            throw new RuntimeException("La ruta de una imagen no es válida.");
        }
        $filePath = $roomImagesDirectory . DIRECTORY_SEPARATOR . $fileName;
        if (is_file($filePath) && !unlink($filePath)) {
            throw new RuntimeException("No fue posible eliminar la imagen {$fileName}.");
        }
    }
}
