-- CRM Vanilla PHP — Initial Seed Data
-- Passwords are hashed with bcrypt (password = 'password')

SET NAMES utf8mb4;

-- ─── Users ───────────────────────────────────────────────────────
INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_active`, `timezone`) VALUES
('Administrador',    'admin@crm.test',    '$2y$12$v2g8L5r/mfrTLaKWr8cRCenudfyVYb7ZQmIMpcimEoH./wbzo.382', 'admin',    1, 'America/La_Paz'),
('Gerente Comercial','gerente@crm.test',  '$2y$12$v2g8L5r/mfrTLaKWr8cRCenudfyVYb7ZQmIMpcimEoH./wbzo.382', 'manager',  1, 'America/La_Paz'),
('Carlos Méndez',    'vendedor@crm.test', '$2y$12$v2g8L5r/mfrTLaKWr8cRCenudfyVYb7ZQmIMpcimEoH./wbzo.382', 'vendedor', 1, 'America/La_Paz');

-- ─── Verticals ───────────────────────────────────────────────────
INSERT INTO `verticals` (`name`, `slug`, `description`, `color`, `is_active`, `track_commission`) VALUES
('Software',   'software',   'Venta de productos y servicios de software', '#3B82F6', 1, 0),
('Consultoría','consultoria','Servicios de consultoría empresarial',        '#10B981', 1, 1),
('Hardware',   'hardware',   'Equipos y componentes tecnológicos',          '#F59E0B', 1, 0);

-- ─── Stages (Software) ───────────────────────────────────────────
INSERT INTO `stages` (`vertical_id`, `name`, `color`, `position`, `is_won`, `is_lost`) VALUES
(1, 'Prospecto',     '#6B7280', 1, 0, 0),
(1, 'Contactado',    '#3B82F6', 2, 0, 0),
(1, 'Demo',          '#8B5CF6', 3, 0, 0),
(1, 'Propuesta',     '#F59E0B', 4, 0, 0),
(1, 'Negociación',   '#EF4444', 5, 0, 0),
(1, 'Cerrado Ganado','#10B981', 6, 1, 0),
(1, 'Cerrado Perdido','#DC2626',7, 0, 1);

-- ─── Stages (Consultoría) ────────────────────────────────────────
INSERT INTO `stages` (`vertical_id`, `name`, `color`, `position`, `is_won`, `is_lost`) VALUES
(2, 'Lead',          '#6B7280', 1, 0, 0),
(2, 'Diagnóstico',   '#3B82F6', 2, 0, 0),
(2, 'Propuesta',     '#F59E0B', 3, 0, 0),
(2, 'Contrato',      '#10B981', 4, 1, 0),
(2, 'Perdido',       '#DC2626', 5, 0, 1);

-- ─── Stages (Hardware) ───────────────────────────────────────────
INSERT INTO `stages` (`vertical_id`, `name`, `color`, `position`, `is_won`, `is_lost`) VALUES
(3, 'Cotización',    '#6B7280', 1, 0, 0),
(3, 'Aprobación',    '#F59E0B', 2, 0, 0),
(3, 'Entregado',     '#10B981', 3, 1, 0),
(3, 'Cancelado',     '#DC2626', 4, 0, 1);

-- ─── Clients ─────────────────────────────────────────────────────
INSERT INTO `clients` (`name`, `company_name`, `email`, `phone`, `industry`) VALUES
('Juan Pérez',    'Empresa Alfa S.A.',  'juan@alfa.com',   '+591 70012345', 'Finanzas'),
('María García',  'Beta Corp',          'maria@beta.com',  '+591 70099887', 'Salud'),
('Roberto Lara',  'Gamma Solutions',    'roberto@gamma.bo','+591 71234567', 'Tecnología'),
('Ana Torres',    NULL,                 'ana@gmail.com',   '+591 72345678', 'Educación'),
('Luis Mendoza',  'Delta Industries',   'luis@delta.com',  '+591 73456789', 'Manufactura');

-- ─── Deals ───────────────────────────────────────────────────────
INSERT INTO `deals` (`title`, `client_id`, `vertical_id`, `stage_id`, `assigned_to`, `amount`, `currency`, `probability`, `expected_close_date`, `notes`) VALUES
('Sistema ERP módulo contabilidad',    1, 1, 2, 3, 45000.00, 'BOB', 60, DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'Cliente interesado en módulo de reportes'),
('Licencias Office 365 x50',           2, 1, 3, 3, 12500.00, 'USD', 75, DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'Demo agendada para la próxima semana'),
('Consultoría transformación digital',  3, 2, 9, 2, 80000.00, 'BOB', 50, DATE_ADD(CURDATE(), INTERVAL 45 DAY), 'Proyecto de 3 meses'),
('Servidores rack x4',                  4, 3, 13,3, 22000.00, 'USD', 80, DATE_ADD(CURDATE(), INTERVAL 7  DAY), 'Urgente — data center ampliación'),
('Desarrollo app móvil',               5, 1, 4, 3, 35000.00, 'BOB', 40, DATE_ADD(CURDATE(), INTERVAL 60 DAY), '');
