<?php
// api/products.php
include_once 'config/database.php';
include_once 'config/core.php';

$user = authenticate();
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // If not superadmin, only show entity's products
    $where = "";
    if ($user['role'] !== 'superadmin') {
        $where = "WHERE p.entity_id = :entity_id";
    }

    $query = "SELECT p.id, p.internal_code, p.serial_number, p.brand, p.model, p.status, pt.name as type_name, e.name as entity_name
              FROM products p
              LEFT JOIN product_types pt ON p.product_type_id = pt.id
              LEFT JOIN entities e ON p.entity_id = e.id
              $where";

    $stmt = $db->prepare($query);

    if ($user['role'] !== 'superadmin') {
        $stmt->bindParam(':entity_id', $user['entity_id']);
    }

    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($products) > 0) {
        $product_ids = array_column($products, 'id');
        $in_clause = implode(',', array_fill(0, count($product_ids), '?'));

        // Fetch dynamic attributes for the fetched products only
        $attr_query = "SELECT pav.product_id, da.name, pav.value_text
                       FROM product_attribute_values pav
                       JOIN dynamic_attributes da ON pav.attribute_id = da.id
                       WHERE pav.product_id IN ($in_clause)";
        $attr_stmt = $db->prepare($attr_query);
        $attr_stmt->execute($product_ids);
        $attributes = $attr_stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $attributes = [];
    }

    // Group attributes by product_id
    $grouped_attributes = [];
    foreach ($attributes as $attr) {
        $grouped_attributes[$attr['product_id']][] = [
            'name' => $attr['name'],
            'value' => $attr['value_text']
        ];
    }

    // Attach attributes to products
    foreach ($products as &$product) {
        $product['attributes'] = $grouped_attributes[$product['id']] ?? [];
    }

    sendJsonResponse(200, $products);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Must be at least technician to create
    if ($user['role'] === 'viewer') {
        sendJsonResponse(403, ['message' => 'Forbidden']);
    }

    $data = json_decode(file_get_contents("php://input"));

    // basic validation
    if(empty($data->product_type_id) || empty($data->internal_code)) {
         sendJsonResponse(400, ['message' => 'Missing data']);
    }

    // entity_id is derived from session unless superadmin
    $entity_id = $user['entity_id'];
    if ($user['role'] === 'superadmin') {
        if (!empty($data->entity_id)) {
            $entity_id = $data->entity_id;
        } else {
            sendJsonResponse(400, ['message' => 'Superadmin must specify an entity_id for the product']);
        }
    }

    $query = "INSERT INTO products (entity_id, product_type_id, internal_code, serial_number, brand, model, status)
              VALUES (:entity_id, :product_type_id, :internal_code, :serial_number, :brand, :model, 'in_stock')";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':entity_id', $entity_id);
    $stmt->bindParam(':product_type_id', $data->product_type_id);
    $stmt->bindParam(':internal_code', $data->internal_code);
    $stmt->bindParam(':serial_number', $data->serial_number);
    $stmt->bindParam(':brand', $data->brand);
    $stmt->bindParam(':model', $data->model);

    if ($stmt->execute()) {
        $product_id = $db->lastInsertId();

        // Handle dynamic attributes if provided
        if (!empty($data->attributes) && is_object($data->attributes)) {
            $attr_query = "INSERT INTO product_attribute_values (product_id, attribute_id, value_text) VALUES (:product_id, :attribute_id, :value_text)";
            $attr_stmt = $db->prepare($attr_query);

            foreach ($data->attributes as $attr_id => $attr_value) {
                $attr_stmt->bindParam(':product_id', $product_id);
                $attr_stmt->bindParam(':attribute_id', $attr_id);
                $attr_stmt->bindParam(':value_text', $attr_value);
                $attr_stmt->execute();
            }
        }

        sendJsonResponse(201, ['message' => 'Product created', 'id' => $product_id]);
    } else {
        sendJsonResponse(500, ['message' => 'Failed to create product']);
    }
}
?>