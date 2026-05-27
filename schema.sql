-- Database Schema for Support Products Management (Multitenant)

-- 1. Entities (The different organizations/clinics that use the system)
CREATE TABLE IF NOT EXISTS entities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Users (Superadmins, Entity Admins, Technicians, Viewers)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_id INT NULL, -- NULL if Superadmin
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'entity_admin', 'technician', 'viewer') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entity_id) REFERENCES entities(id) ON DELETE CASCADE
);

-- 3. Product Types (e.g., Cama articulada, Silla de ruedas eléctrica)
CREATE TABLE IF NOT EXISTS product_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entity_id) REFERENCES entities(id) ON DELETE CASCADE
);

-- 4. Dynamic Attributes Definition (e.g., "Motor Power", "Width")
CREATE TABLE IF NOT EXISTS dynamic_attributes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_type_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('text', 'number', 'boolean', 'date') NOT NULL DEFAULT 'text',
    FOREIGN KEY (product_type_id) REFERENCES product_types(id) ON DELETE CASCADE
);

-- 5. Products (The actual physical items)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_id INT NOT NULL,
    product_type_id INT NOT NULL,
    internal_code VARCHAR(100) NOT NULL,
    serial_number VARCHAR(100),
    brand VARCHAR(100),
    model VARCHAR(100),
    status ENUM('in_stock', 'assigned', 'in_repair', 'retired') DEFAULT 'in_stock',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entity_id) REFERENCES entities(id) ON DELETE CASCADE,
    FOREIGN KEY (product_type_id) REFERENCES product_types(id) ON DELETE RESTRICT
);

-- 6. Product Attribute Values (The values for the dynamic attributes of a specific product)
CREATE TABLE IF NOT EXISTS product_attribute_values (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    attribute_id INT NOT NULL,
    value_text TEXT,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (attribute_id) REFERENCES dynamic_attributes(id) ON DELETE CASCADE
);

-- 7. End Users / Patients (Who receives the products)
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    contact_info VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entity_id) REFERENCES entities(id) ON DELETE CASCADE
);

-- 8. Assignments (History of who had what, when, and where)
CREATE TABLE IF NOT EXISTS assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    patient_id INT NULL, -- NULL could mean it was moved back to stock/warehouse
    assigned_by INT NOT NULL, -- User ID who did the assignment
    location TEXT NOT NULL, -- "Almacen Principal", "Domicilio del paciente", etc.
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    returned_at TIMESTAMP NULL, -- When it was returned
    notes TEXT,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE RESTRICT
);

-- 9. Incidents (Tracking problems with products)
CREATE TABLE IF NOT EXISTS incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    reported_by INT NOT NULL, -- User ID
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE RESTRICT
);
