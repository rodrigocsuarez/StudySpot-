#-- Se a base de dados já existir e precisares de a limpar para aplicar o novo esquema:
DROP DATABASE IF EXISTS studyspot_db;

#-- 1. Criar a Base de Dados
CREATE DATABASE IF NOT EXISTS studyspot_db;
USE studyspot_db;

-- 2. -- 3. Tabela de Utilizadores 
CREATE TABLE utilizadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user', 
    pref_ruido INT DEFAULT 3,
    pref_wifi INT DEFAULT 3,
    pref_tomadas INT DEFAULT 3,
    pref_lotacao INT DEFAULT 3,
    data_registo TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabela de Spots (os locais)
CREATE TABLE spots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    tipo ENUM('Café', 'Biblioteca', 'Cowork') NOT NULL,
    lat DECIMAL(10, 8) NOT NULL,
    lng DECIMAL(11, 8) NOT NULL,
    criado_por INT,
    FOREIGN KEY (criado_por) REFERENCES utilizadores(id)
);

-- 4. Tabela de Reviews
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    spot_id INT NOT NULL,
    user_id INT NOT NULL,
    nota_ruido INT CHECK (nota_ruido BETWEEN 1 AND 5),
    nota_wifi INT CHECK (nota_wifi BETWEEN 1 AND 5),
    nota_tomadas INT CHECK (nota_tomadas BETWEEN 1 AND 5),
    nota_lotacao INT CHECK (nota_lotacao BETWEEN 1 AND 5), 
    comentario TEXT,
    data_review TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (spot_id) REFERENCES spots(id),
    FOREIGN KEY (user_id) REFERENCES utilizadores(id)
);