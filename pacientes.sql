-- Esquema para PostgreSQL (Neon)

CREATE TABLE IF NOT EXISTS pacientes (
  id SERIAL PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  ubicacion VARCHAR(30) NOT NULL DEFAULT 'Recepción'
    CHECK (ubicacion IN ('Recepción', 'Sala de preparación', 'Cirugía', 'Sala de recuperación')),
  fecha_registro TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

INSERT INTO pacientes (id, nombre, ubicacion, fecha_registro) VALUES
(1, 'CARLOS ARANGO', 'Sala de preparación', '2026-09-24 15:16:33-05'),
(2, 'LAURA GÓMEZ', 'Cirugía', '2026-09-24 15:16:33-05'),
(3, 'YULIETH GALVIS', 'Recepción', '2026-09-24 15:33:27-05'),
(5, 'MARTHA SOTO', 'Sala de recuperación', '2026-09-24 19:48:44-05'),
(6, 'DAYANA ALZATE', 'Sala de recuperación', '2026-09-24 19:56:32-05'),
(7, 'MARIA DANIELA ROSAS', 'Cirugía', '2026-09-25 16:17:32-05'),
(8, 'PAOLA RUÍZ', 'Recepción', '2026-09-25 16:19:28-05'),
(9, 'DANIELA MANRRIQUE', 'Sala de preparación', '2026-09-28 19:33:28-05'),
(10, 'TATIANA PULGARIN', 'Cirugía', '2026-09-28 19:40:40-05'),
(11, 'JULIANA', 'Sala de preparación', '2026-10-05 20:14:34-05'),
(12, 'DANIELA MANRRIQUE', 'Sala de preparación', '2026-10-05 20:45:36-05')
ON CONFLICT (id) DO NOTHING;

SELECT setval('pacientes_id_seq', (SELECT MAX(id) FROM pacientes));
