/* =========================================================
   DB: Fornitori / Pezzi / Catalogo
   Schema (SQL standard, compatibile con PostgreSQL/MySQL con minime modifiche)
   ========================================================= */

/* (Opzionale) crea uno schema / database
-- PostgreSQL:
CREATE SCHEMA IF NOT EXISTS magazzino;
SET search_path TO magazzino;

-- MySQL:
CREATE DATABASE IF NOT EXISTS magazzino;
USE magazzino;
*/

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
   QUERY (1..10)
   ========================================================= */

/* 1) Trovare i pnome dei pezzi per cui esiste un qualche fornitore */
SELECT DISTINCT p.pnome
FROM Pezzi p
JOIN Catalogo c ON c.pid = p.pid;

/* 2) Trovare gli fnome dei fornitori che forniscono ogni pezzo */
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

/* 5) Trovare i fid dei fornitori che ricaricano su alcuni pezzi più del costo
      medio di quel pezzo */
SELECT DISTINCT c.fid
FROM Catalogo c
WHERE c.costo > (
  SELECT AVG(c2.costo)
  FROM Catalogo c2
  WHERE c2.pid = c.pid
);

/* 6) Per ciascun pezzo, trovare gli fnome dei fornitori che ricaricano di più su quel pezzo
      (restituisco anche pid/pnome e costo massimo) */
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

/* 7) Trovare i fid dei fornitori che forniscono solo pezzi rossi
      (cioè: forniscono almeno un pezzo e nessun pezzo non-rosso) */
SELECT DISTINCT c.fid
FROM Catalogo c
WHERE NOT EXISTS (
  SELECT 1
  FROM Catalogo c2
  JOIN Pezzi p2 ON p2.pid = c2.pid
  WHERE c2.fid = c.fid
    AND p2.colore <> 'rosso'
);

/* 8) Trovare i fid dei fornitori che forniscono un pezzo rosso e un pezzo verde */
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

/* 9) Trovare i fid dei fornitori che forniscono un pezzo rosso o uno verde */
SELECT DISTINCT c.fid
FROM Catalogo c
JOIN Pezzi p ON p.pid = c.pid
WHERE p.colore IN ('rosso', 'verde');

/* 10) Trovare i pid dei pezzi forniti da almeno due fornitori */
SELECT c.pid
FROM Catalogo c
GROUP BY c.pid
HAVING COUNT(DISTINCT c.fid) >= 2;