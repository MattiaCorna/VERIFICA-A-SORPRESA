/* =========================================================
   DB: Fornitori / Pezzi / Catalogo
   Schema (SQL standard, compatibile con PostgreSQL/MySQL con minime modifiche)
   ========================================================= */

DROP TABLE IF EXISTS Catalogo;
DROP TABLE IF EXISTS Pezzi;
DROP TABLE IF EXISTS Fornitori;

CREATE TABLE Fornitori (
  fid       VARCHAR(50) PRIMARY KEY,
  fnome     VARCHAR(100) NOT NULL,
  indirizzo VARCHAR(200)
);

CREATE TABLE Pezzi (
  pid    VARCHAR(50) PRIMARY KEY,
  pnome  VARCHAR(100) NOT NULL,
  colore VARCHAR(30)  NOT NULL
);

CREATE TABLE Catalogo (
  fid   VARCHAR(50) NOT NULL,
  pid   VARCHAR(50) NOT NULL,
  costo REAL        NOT NULL,
  PRIMARY KEY (fid, pid),
  FOREIGN KEY (fid) REFERENCES Fornitori(fid),
  FOREIGN KEY (pid) REFERENCES Pezzi(pid)
);

/* =========================================================
   DATI DI ESEMPIO - FORNITORI
   ========================================================= */
INSERT INTO Fornitori VALUES
('F01', 'Acme', 'Via Roma 1, Milano'),
('F02', 'WidgetCorp', 'Via Milano 2, Torino'),
('F03', 'Supplies Inc', 'Via Torino 3, Genova'),
('F04', 'TechParts', 'Via Venezia 4, Venezia'),
('F05', 'MegaSupplies', 'Via Napoli 5, Napoli'),
('F06', 'GreenTech', 'Via Palermo 6, Palermo');

/* =========================================================
   DATI DI ESEMPIO - PEZZI
   ========================================================= */
INSERT INTO Pezzi VALUES
('P01', 'Bullone', 'rosso'),
('P02', 'Vite', 'blu'),
('P03', 'Dado', 'rosso'),
('P04', 'Rivetto', 'verde'),
('P05', 'Molla', 'blu'),
('P06', 'Guarnizione', 'rosso'),
('P07', 'Cuscinetto', 'verde'),
('P08', 'Cavo', 'blu'),
('P09', 'Resistore', 'rosso'),
('P10', 'Condensatore', 'verde');

/* =========================================================
   DATI DI ESEMPIO - CATALOGO
   ========================================================= */
INSERT INTO Catalogo VALUES
/* Acme (F01): fornisce TUTTI P01-P10 */
('F01', 'P01', 10.5),
('F01', 'P02', 5.0),
('F01', 'P03', 8.5),
('F01', 'P04', 6.0),
('F01', 'P05', 7.2),
('F01', 'P06', 9.0),
('F01', 'P07', 12.0),
('F01', 'P08', 4.5),
('F01', 'P09', 15.0),
('F01', 'P10', 8.5),

/* WidgetCorp (F02): fornisce TUTTI P01-P10 */
('F02', 'P04', 6.8),
('F02', 'P05', 7.1),
('F02', 'P06', 8.8),
('F02', 'P01', 11.0),
('F02', 'P02', 5.2),
('F02', 'P03', 8.2),
('F02', 'P07', 11.5),
('F02', 'P08', 4.2),
('F02', 'P09', 16.0),
('F02', 'P10', 9.2),

/* Supplies Inc (F03): fornisce TUTTI P01-P10 */
('F03', 'P07', 13.0),
('F03', 'P08', 3.9),
('F03', 'P09', 15.0),
('F03', 'P10', 10.0),
('F03', 'P01', 9.8),
('F03', 'P02', 4.5),
('F03', 'P03', 8.2),
('F03', 'P04', 5.8),
('F03', 'P05', 6.8),
('F03', 'P06', 8.5);

/* =========================================================
   QUERY (1..10)
   ========================================================= */

/* 1) Trovare i pnome dei pezzi per cui esiste un qualche fornitore */
SELECT DISTINCT p.pnome
FROM Pezzi p
JOIN Catalogo c ON c.pid = p.pid;

/* 2) Trovare gli fnome dei fornitori che forniscono OGNI pezzo */
SELECT f.fnome
FROM Fornitori f
WHERE NOT EXISTS (
  SELECT 1
  FROM Pezzi p
  WHERE NOT EXISTS (
    SELECT 1
    FROM Catalogo c
    WHERE c.fid = f.fid
      AND c.pid = p.pid
  )
);

/* 3) Trovare gli fnome dei fornitori che forniscono tutti i pezzi rossi */
SELECT f.fnome
FROM Fornitori f
WHERE NOT EXISTS (
  SELECT 1
  FROM Pezzi p
  WHERE p.colore = 'rosso'
    AND NOT EXISTS (
      SELECT 1
      FROM Catalogo c
      WHERE c.fid = f.fid
        AND c.pid = p.pid
    )
);

/* 4) Trovare i pnome dei pezzi forniti dalla Acme e da nessun altro */
SELECT DISTINCT p.pnome
FROM Pezzi p
JOIN Catalogo c  ON c.pid = p.pid
JOIN Fornitori f ON f.fid = c.fid
WHERE f.fnome = 'Acme'
  AND NOT EXISTS (
    SELECT 1
    FROM Catalogo c2
    WHERE c2.pid = p.pid
      AND c2.fid <> c.fid
  );

/* 5) Trovare i fid dei fornitori che ricaricano su alcuni pezzi più del costo medio */
SELECT DISTINCT c.fid
FROM Catalogo c
WHERE c.costo > (
  SELECT AVG(c2.costo)
  FROM Catalogo c2
  WHERE c2.pid = c.pid
);

/* 6) Per ciascun pezzo, trovare gli fnome dei fornitori che ricaricano di più */
SELECT p.pid, p.pnome, f.fnome, c.costo
FROM Catalogo c
JOIN Pezzi p     ON p.pid = c.pid
JOIN Fornitori f ON f.fid = c.fid
WHERE NOT EXISTS (
  SELECT 1
  FROM Catalogo c2
  WHERE c2.pid = c.pid
    AND c2.costo > c.costo
);

/* 7) Trovare i fid dei fornitori che forniscono SOLO pezzi rossi */
SELECT DISTINCT c.fid
FROM Catalogo c
WHERE NOT EXISTS (
  SELECT 1
  FROM Catalogo c2
  JOIN Pezzi p2 ON p2.pid = c2.pid
  WHERE c2.fid = c.fid
    AND p2.colore <> 'rosso'
);

/* 8) Trovare i fid dei fornitori che forniscono un pezzo rosso E uno verde */
SELECT f.fid
FROM Fornitori f
WHERE EXISTS (
  SELECT 1
  FROM Catalogo c
  JOIN Pezzi p ON p.pid = c.pid
  WHERE c.fid = f.fid
    AND p.colore = 'rosso'
)
AND EXISTS (
  SELECT 1
  FROM Catalogo c
  JOIN Pezzi p ON p.pid = c.pid
  WHERE c.fid = f.fid
    AND p.colore = 'verde'
);

/* 9) Trovare i fid dei fornitori che forniscono un pezzo rosso O uno verde */
SELECT DISTINCT c.fid
FROM Catalogo c
JOIN Pezzi p ON p.pid = c.pid
WHERE p.colore IN ('rosso', 'verde');

/* 10) Trovare i pid dei pezzi forniti da almeno due fornitori */
SELECT c.pid
FROM Catalogo c
GROUP BY c.pid
HAVING COUNT(DISTINCT c.fid) >= 2;