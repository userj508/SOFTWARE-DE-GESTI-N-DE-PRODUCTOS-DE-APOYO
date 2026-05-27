-- Seed data for testing
USE support_products;

INSERT INTO entities (name) VALUES ('Cruz Roja');
INSERT INTO entities (name) VALUES ('Hospital San Juan');

-- password is 'admin' (password_hash is just 'admin' for demo ease based on the logic in auth.php)
INSERT INTO users (entity_id, name, email, password_hash, role) VALUES
(NULL, 'Super Admin', 'super@admin.com', 'admin', 'superadmin'),
(1, 'Admin Cruz Roja', 'admin@cr.com', 'admin', 'entity_admin'),
(1, 'Tech Cruz Roja', 'tech@cr.com', 'admin', 'technician'),
(2, 'Admin Hospital', 'admin@hosp.com', 'admin', 'entity_admin');

INSERT INTO product_types (entity_id, name, description) VALUES
(1, 'Silla de ruedas manual', 'Silla standard'),
(1, 'Cama Articulada', 'Cama con motor'),
(2, 'Grúa de traslado', 'Grúa para pacientes');

INSERT INTO products (entity_id, product_type_id, internal_code, serial_number, brand, model, status) VALUES
(1, 1, 'CR-001', 'SN12345', 'Invacare', 'Action 3NG', 'in_stock'),
(1, 2, 'CR-002', 'SN98765', 'Sunrise', 'Medical Bed', 'assigned'),
(2, 3, 'HSJ-001', 'GR-99', 'Wissner', 'Lifter', 'in_stock');

INSERT INTO dynamic_attributes (product_type_id, name, type) VALUES
(1, 'Ancho de asiento', 'number'),
(1, 'Plegable', 'boolean'),
(2, 'Número de motores', 'number');

INSERT INTO patients (entity_id, name, address, contact_info) VALUES
(1, 'Juan Pérez', 'Calle Mayor 1', '555-1234'),
(1, 'María García', 'Avenida Libertad 5', '555-9876');

INSERT INTO assignments (product_id, patient_id, assigned_by, location, notes) VALUES
(2, 1, 3, 'Domicilio del paciente', 'Entregada con manual de instrucciones');

INSERT INTO product_attribute_values (product_id, attribute_id, value_text) VALUES
(1, 1, '45'),
(1, 2, 'Si'),
(2, 3, '2');

INSERT INTO incidents (product_id, reported_by, title, description, status) VALUES
(2, 3, 'Motor no funciona', 'El motor para subir la cama hace un ruido extraño y se para', 'open');
