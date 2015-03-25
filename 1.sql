SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `lavado` DEFAULT CHARACTER SET utf8 ;
USE `lavado` ;

-- -----------------------------------------------------
-- Table `lavado`.`tipoidentificacion`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`tipoidentificacion` (
  `idtipoidentificacion` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(256) NOT NULL,
  `abreviatura` VARCHAR(10) NOT NULL,
  `espersonanatural` TINYINT(1) NOT NULL DEFAULT 1,
  `estado` TINYINT(1) NOT NULL DEFAULT true,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idtipoidentificacion`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`pais`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`pais` (
  `idpais` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(256) NOT NULL,
  `nacionalidad` VARCHAR(256) NOT NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idpais`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`persona`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`persona` (
  `idpersona` INT NOT NULL AUTO_INCREMENT,
  `idtipoidentificacion` INT NOT NULL,
  `identificacion` VARCHAR(256) NOT NULL,
  `nombres` VARCHAR(256) NOT NULL,
  `direccion` VARCHAR(256) NOT NULL,
  `telefonos` VARCHAR(256) NOT NULL,
  `email` VARCHAR(256) NULL,
  `idpais` INT NOT NULL COMMENT 'Nacionalidad de la persona',
  `fechanacimiento` DATE NULL,
  `sexo` INT NULL DEFAULT 0,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idpersona`),
  INDEX `fk_persona_tipoidentificacion_idx` (`idtipoidentificacion` ASC),
  INDEX `fk_persona_pais1_idx` (`idpais` ASC),
  CONSTRAINT `fk_persona_tipoidentificacion`
    FOREIGN KEY (`idtipoidentificacion`)
    REFERENCES `lavado`.`tipoidentificacion` (`idtipoidentificacion`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_persona_pais1`
    FOREIGN KEY (`idpais`)
    REFERENCES `lavado`.`pais` (`idpais`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`departamento`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`departamento` (
  `iddepartamento` INT NOT NULL AUTO_INCREMENT,
  `idpais` INT NOT NULL,
  `nombre` VARCHAR(256) NOT NULL,
  `gentilicio` VARCHAR(256) NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`iddepartamento`),
  INDEX `fk_departamento_pais1_idx` (`idpais` ASC),
  CONSTRAINT `fk_departamento_pais1`
    FOREIGN KEY (`idpais`)
    REFERENCES `lavado`.`pais` (`idpais`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`municipio`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`municipio` (
  `idmunicipio` INT NOT NULL AUTO_INCREMENT,
  `iddepartamento` INT NOT NULL,
  `nombre` VARCHAR(256) NOT NULL,
  `gentilicio` VARCHAR(256) NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idmunicipio`),
  INDEX `fk_municipio_departamento1_idx` (`iddepartamento` ASC),
  CONSTRAINT `fk_municipio_departamento1`
    FOREIGN KEY (`iddepartamento`)
    REFERENCES `lavado`.`departamento` (`iddepartamento`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`usuario`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`usuario` (
  `idusuario` INT NOT NULL AUTO_INCREMENT,
  `idpersona` INT NOT NULL,
  `login` VARCHAR(256) NOT NULL,
  `password` VARCHAR(256) NOT NULL,
  `estado` TINYINT(1) NULL DEFAULT 1,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idusuario`),
  INDEX `fk_usuario_persona1_idx` (`idpersona` ASC),
  CONSTRAINT `fk_usuario_persona1`
    FOREIGN KEY (`idpersona`)
    REFERENCES `lavado`.`persona` (`idpersona`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`modulo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`modulo` (
  `idmodulo` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(256) NOT NULL,
  `clase` VARCHAR(256) NULL DEFAULT NULL,
  `idmodulopadre` INT(11) NOT NULL,
  `orden` INT(11) NOT NULL DEFAULT '0',
  `iconcss` VARCHAR(64) NULL DEFAULT '',
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idmodulo`),
  INDEX `nombre` (`nombre`(255) ASC),
  INDEX `clase` (`clase`(255) ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 10115
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `lavado`.`accionauditable`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`accionauditable` (
  `idaccionauditable` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(256) NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idaccionauditable`),
  INDEX `nombre` (`nombre` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`auditoria`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`auditoria` (
  `idauditoria` INT NOT NULL AUTO_INCREMENT,
  `idaccionauditable` INT NOT NULL,
  `idusuario` INT NOT NULL,
  `idmodulo` INT NOT NULL,
  `descripcion` TEXT NOT NULL,
  `fecha` DATETIME NOT NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idauditoria`),
  INDEX `fk_auditoria_modulo1_idx` (`idmodulo` ASC),
  INDEX `fk_auditoria_usuario1_idx` (`idusuario` ASC),
  INDEX `fk_auditoria_accionauditable1_idx` (`idaccionauditable` ASC),
  CONSTRAINT `fk_auditoria_modulo1`
    FOREIGN KEY (`idmodulo`)
    REFERENCES `lavado`.`modulo` (`idmodulo`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_auditoria_usuario1`
    FOREIGN KEY (`idusuario`)
    REFERENCES `lavado`.`usuario` (`idusuario`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_auditoria_accionauditable1`
    FOREIGN KEY (`idaccionauditable`)
    REFERENCES `lavado`.`accionauditable` (`idaccionauditable`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`clase`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`clase` (
  `idclase` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(64) NULL,
  `tabla` VARCHAR(64) NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idclase`),
  UNIQUE INDEX `nombre` (`nombre` ASC),
  UNIQUE INDEX `tabla` (`tabla` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`modificacion`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`modificacion` (
  `idmodificacion` INT NOT NULL AUTO_INCREMENT,
  `idauditoria` INT NOT NULL,
  `idaccionauditable` INT NOT NULL,
  `idclase` INT NOT NULL,
  `descripcion` TEXT NOT NULL,
  `fecha` DATETIME NOT NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idmodificacion`),
  INDEX `fk_modificacion_auditoria1_idx` (`idauditoria` ASC),
  INDEX `fk_modificacion_clase1_idx` (`idclase` ASC),
  INDEX `fk_modificacion_accionauditable1_idx` (`idaccionauditable` ASC),
  CONSTRAINT `fk_modificacion_auditoria1`
    FOREIGN KEY (`idauditoria`)
    REFERENCES `lavado`.`auditoria` (`idauditoria`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_modificacion_clase1`
    FOREIGN KEY (`idclase`)
    REFERENCES `lavado`.`clase` (`idclase`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_modificacion_accionauditable1`
    FOREIGN KEY (`idaccionauditable`)
    REFERENCES `lavado`.`accionauditable` (`idaccionauditable`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`perfil`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`perfil` (
  `idperfil` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(256) NOT NULL,
  `estado` TINYINT(1) NULL DEFAULT 1,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idperfil`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`perfilusuario`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`perfilusuario` (
  `idperfilusuario` INT NOT NULL AUTO_INCREMENT,
  `idusuario` INT NOT NULL,
  `idperfil` INT NOT NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idperfilusuario`),
  INDEX `fk_perfilusuario_usuario1_idx` (`idusuario` ASC),
  INDEX `fk_perfilusuario_perfil1_idx` (`idperfil` ASC),
  CONSTRAINT `fk_perfilusuario_usuario1`
    FOREIGN KEY (`idusuario`)
    REFERENCES `lavado`.`usuario` (`idusuario`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_perfilusuario_perfil1`
    FOREIGN KEY (`idperfil`)
    REFERENCES `lavado`.`perfil` (`idperfil`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`moduloperfil`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`moduloperfil` (
  `idmoduloperfil` INT NOT NULL AUTO_INCREMENT,
  `idperfil` INT NOT NULL,
  `idmodulo` INT NOT NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idmoduloperfil`),
  INDEX `fk_modulosperfil_perfil1_idx` (`idperfil` ASC),
  INDEX `fk_modulo_idx` (`idmodulo` ASC),
  CONSTRAINT `fk_modulosperfil_perfil1`
    FOREIGN KEY (`idperfil`)
    REFERENCES `lavado`.`perfil` (`idperfil`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_modulo`
    FOREIGN KEY (`idmodulo`)
    REFERENCES `lavado`.`modulo` (`idmodulo`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`tiporubro`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`tiporubro` (
  `idtiporubro` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(256) NOT NULL,
  `estado` TINYINT(1) NOT NULL DEFAULT true,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idtiporubro`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`tipoautomotor`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`tipoautomotor` (
  `idtipoautomotor` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(256) NULL,
  `estado` TINYINT(1) NULL DEFAULT true,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idtipoautomotor`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`rubro`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`rubro` (
  `idrubro` INT NOT NULL AUTO_INCREMENT,
  `idtiporubro` INT NOT NULL,
  `idtipoautomotor` INT NOT NULL,
  `porcentajeiva` FLOAT NULL,
  `descripcion` VARCHAR(256) NOT NULL,
  `estado` TINYINT(1) NOT NULL DEFAULT 1,
  `valorunitario` FLOAT NOT NULL DEFAULT 0.0,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  `visible` TINYINT(1) NULL DEFAULT 1,
  PRIMARY KEY (`idrubro`),
  INDEX `fk_rubro_tiporubro1` (`idtiporubro` ASC),
  INDEX `fk_rubro_tipoautomotor1_idx` (`idtipoautomotor` ASC),
  CONSTRAINT `fk_rubro_tiporubro10`
    FOREIGN KEY (`idtiporubro`)
    REFERENCES `lavado`.`tiporubro` (`idtiporubro`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_rubro_tipoautomotor1`
    FOREIGN KEY (`idtipoautomotor`)
    REFERENCES `lavado`.`tipoautomotor` (`idtipoautomotor`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`categoriavariable`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`categoriavariable` (
  `idcategoriavariable` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(256) NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idcategoriavariable`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`variable`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`variable` (
  `idvariable` INT NOT NULL AUTO_INCREMENT,
  `idcategoriavariable` INT NOT NULL,
  `nombre` VARCHAR(256) NULL,
  `valor` TEXT NULL,
  `deusuario` TINYINT(1) NULL DEFAULT 0,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idvariable`),
  INDEX `fk_variable_categoriavariable1_idx` (`idcategoriavariable` ASC),
  CONSTRAINT `fk_variable_categoriavariable1`
    FOREIGN KEY (`idcategoriavariable`)
    REFERENCES `lavado`.`categoriavariable` (`idcategoriavariable`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`variableusuario`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`variableusuario` (
  `idvariableusuario` INT NOT NULL AUTO_INCREMENT,
  `idusuario` INT NOT NULL,
  `idvariable` INT NOT NULL,
  `valor` VARCHAR(256) NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idvariableusuario`),
  INDEX `fk_variableusuario_variable1_idx` (`idvariable` ASC),
  INDEX `fk_variableusuario_usuario1_idx` (`idusuario` ASC),
  CONSTRAINT `fk_variableusuario_variable1`
    FOREIGN KEY (`idvariable`)
    REFERENCES `lavado`.`variable` (`idvariable`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_variableusuario_usuario1`
    FOREIGN KEY (`idusuario`)
    REFERENCES `lavado`.`usuario` (`idusuario`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`lenguajeimpresion`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`lenguajeimpresion` (
  `idlenguajeimpresion` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(256) NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idlenguajeimpresion`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`comandoimpresion`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`comandoimpresion` (
  `idcomandoimpresion` INT NOT NULL AUTO_INCREMENT,
  `abreviatura` VARCHAR(32) NOT NULL,
  `descripcion` VARCHAR(256) NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idcomandoimpresion`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`secuenciaimpresion`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`secuenciaimpresion` (
  `idsecuenciaimpresion` INT NOT NULL AUTO_INCREMENT,
  `idlenguajeimpresion` INT NOT NULL,
  `idcomandoimpresion` INT NOT NULL,
  `secuencia` VARCHAR(256) NULL,
  `descripcion` VARCHAR(256) NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idsecuenciaimpresion`),
  INDEX `fk_secuenciaimpresion_lenguajeimpresion1_idx` (`idlenguajeimpresion` ASC),
  INDEX `fk_secuenciaimpresion_comandoimpresion1_idx` (`idcomandoimpresion` ASC),
  CONSTRAINT `fk_secuenciaimpresion_lenguajeimpresion1`
    FOREIGN KEY (`idlenguajeimpresion`)
    REFERENCES `lavado`.`lenguajeimpresion` (`idlenguajeimpresion`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_secuenciaimpresion_comandoimpresion1`
    FOREIGN KEY (`idcomandoimpresion`)
    REFERENCES `lavado`.`comandoimpresion` (`idcomandoimpresion`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`tipoimpresora`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`tipoimpresora` (
  `idtipoimpresora` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(256) NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idtipoimpresora`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`impresora`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`impresora` (
  `idimpresora` INT NOT NULL AUTO_INCREMENT,
  `idusuario` INT NOT NULL,
  `idlenguajeimpresion` INT NOT NULL,
  `idtipoimpresora` INT NOT NULL,
  `nombre` VARCHAR(256) NULL,
  `descripcion` VARCHAR(256) NULL,
  `offsetx` FLOAT NULL DEFAULT 0.0,
  `offsety` FLOAT NULL DEFAULT 0.0,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idimpresora`),
  INDEX `fk_impresora_usuario1_idx` (`idusuario` ASC),
  INDEX `fk_impresora_lenguajeimpresion1_idx` (`idlenguajeimpresion` ASC),
  INDEX `fk_impresora_tipoimpresora1_idx` (`idtipoimpresora` ASC),
  CONSTRAINT `fk_impresora_usuario1`
    FOREIGN KEY (`idusuario`)
    REFERENCES `lavado`.`usuario` (`idusuario`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_impresora_lenguajeimpresion1`
    FOREIGN KEY (`idlenguajeimpresion`)
    REFERENCES `lavado`.`lenguajeimpresion` (`idlenguajeimpresion`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_impresora_tipoimpresora1`
    FOREIGN KEY (`idtipoimpresora`)
    REFERENCES `lavado`.`tipoimpresora` (`idtipoimpresora`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`documentoimprimible`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`documentoimprimible` (
  `iddocumentoimprimible` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(64) NULL,
  `descripcion` VARCHAR(256) NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`iddocumentoimprimible`),
  UNIQUE INDEX `nombre` (`nombre` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`estadoimpresion`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`estadoimpresion` (
  `idestadoimpresion` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(256) NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idestadoimpresion`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`tipoimpresion`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`tipoimpresion` (
  `idtipoimpresion` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(45) NOT NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idtipoimpresion`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`impresion`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`impresion` (
  `idimpresion` INT NOT NULL AUTO_INCREMENT,
  `idtipoimpresion` INT NOT NULL,
  `idusuario` INT NOT NULL,
  `idimpresora` INT NULL,
  `iddocumentoimprimible` INT NOT NULL,
  `idestadoimpresion` INT NOT NULL,
  `fecha` DATETIME NULL,
  `comentarios` VARCHAR(256) NULL,
  `contenido` LONGTEXT NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idimpresion`),
  INDEX `fk_impresion_usuario1_idx` (`idusuario` ASC),
  INDEX `fk_impresion_impresora1_idx` (`idimpresora` ASC),
  INDEX `fk_impresion_documentoimprimible1_idx` (`iddocumentoimprimible` ASC),
  INDEX `fk_impresion_estadoimpresion1_idx` (`idestadoimpresion` ASC),
  INDEX `fk_impresion_tipoimpresion1_idx` (`idtipoimpresion` ASC),
  CONSTRAINT `fk_impresion_usuario1`
    FOREIGN KEY (`idusuario`)
    REFERENCES `lavado`.`usuario` (`idusuario`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_impresion_impresora1`
    FOREIGN KEY (`idimpresora`)
    REFERENCES `lavado`.`impresora` (`idimpresora`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_impresion_documentoimprimible1`
    FOREIGN KEY (`iddocumentoimprimible`)
    REFERENCES `lavado`.`documentoimprimible` (`iddocumentoimprimible`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_impresion_estadoimpresion1`
    FOREIGN KEY (`idestadoimpresion`)
    REFERENCES `lavado`.`estadoimpresion` (`idestadoimpresion`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_impresion_tipoimpresion1`
    FOREIGN KEY (`idtipoimpresion`)
    REFERENCES `lavado`.`tipoimpresion` (`idtipoimpresion`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`empresa`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`empresa` (
  `idempresa` INT NOT NULL AUTO_INCREMENT,
  `idpersona` INT NOT NULL,
  `idmunicipio` INT NOT NULL,
  `nombreabreviado` VARCHAR(256) NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idempresa`),
  INDEX `fk_notaria_persona1_idx` (`idpersona` ASC),
  INDEX `fk_notaria_municipio1_idx` (`idmunicipio` ASC),
  CONSTRAINT `fk_notaria_persona1`
    FOREIGN KEY (`idpersona`)
    REFERENCES `lavado`.`persona` (`idpersona`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_notaria_municipio1`
    FOREIGN KEY (`idmunicipio`)
    REFERENCES `lavado`.`municipio` (`idmunicipio`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`cargo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`cargo` (
  `idcargo` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(256) NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  `estado` TINYINT(1) NULL,
  PRIMARY KEY (`idcargo`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`personal`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`personal` (
  `idpersonal` INT NOT NULL AUTO_INCREMENT,
  `idpersona` INT NOT NULL,
  `idcargo` INT NOT NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idpersonal`),
  INDEX `fk_personal_persona1_idx` (`idpersona` ASC),
  INDEX `fk_personal_cargo1_idx` (`idcargo` ASC),
  CONSTRAINT `fk_personal_persona1`
    FOREIGN KEY (`idpersona`)
    REFERENCES `lavado`.`persona` (`idpersona`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_personal_cargo1`
    FOREIGN KEY (`idcargo`)
    REFERENCES `lavado`.`cargo` (`idcargo`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`automotor`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`automotor` (
  `idautomotor` INT NOT NULL AUTO_INCREMENT,
  `idtipoautomotor` INT NOT NULL,
  `modelo` INT NOT NULL DEFAULT 0.0,
  `matricula` VARCHAR(45) NOT NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idautomotor`),
  INDEX `automotormatricula` (`matricula` ASC),
  INDEX `fk_automotor_tipoautomotor1_idx` (`idtipoautomotor` ASC),
  CONSTRAINT `fk_automotor_tipoautomotor1`
    FOREIGN KEY (`idtipoautomotor`)
    REFERENCES `lavado`.`tipoautomotor` (`idtipoautomotor`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`combo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`combo` (
  `idcombo` INT NOT NULL AUTO_INCREMENT,
  `idtipoautomotor` INT NOT NULL,
  `descripcion` VARCHAR(256) NULL,
  `estado` TINYINT(1) NULL DEFAULT 1,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idcombo`),
  INDEX `fk_combo_tipoautomotor1_idx` (`idtipoautomotor` ASC),
  CONSTRAINT `fk_combo_tipoautomotor1`
    FOREIGN KEY (`idtipoautomotor`)
    REFERENCES `lavado`.`tipoautomotor` (`idtipoautomotor`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`rubrocombo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`rubrocombo` (
  `idrubrocombo` INT NOT NULL AUTO_INCREMENT,
  `idcombo` INT NOT NULL,
  `idrubro` INT NOT NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idrubrocombo`),
  INDEX `fk_rubrocombo_combo1_idx` (`idcombo` ASC),
  INDEX `fk_rubrocombo_rubro1_idx` (`idrubro` ASC),
  CONSTRAINT `fk_rubrocombo_combo1`
    FOREIGN KEY (`idcombo`)
    REFERENCES `lavado`.`combo` (`idcombo`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_rubrocombo_rubro1`
    FOREIGN KEY (`idrubro`)
    REFERENCES `lavado`.`rubro` (`idrubro`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`estadoservicio`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`estadoservicio` (
  `idestadoservicio` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(256) NULL,
  `estado` TINYINT(1) NULL DEFAULT 1,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idestadoservicio`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`empleado`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`empleado` (
  `idempleado` INT NOT NULL AUTO_INCREMENT,
  `idpersona` INT NOT NULL,
  `estado` TINYINT(1) NULL DEFAULT 1,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  `observaciones` VARCHAR(1024) NULL,
  PRIMARY KEY (`idempleado`),
  INDEX `fk_empleado_persona1_idx` (`idpersona` ASC),
  CONSTRAINT `fk_empleado_persona1`
    FOREIGN KEY (`idpersona`)
    REFERENCES `lavado`.`persona` (`idpersona`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`servicio`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`servicio` (
  `idservicio` INT NOT NULL AUTO_INCREMENT,
  `idusuario` INT NOT NULL,
  `idestadoservicio` INT NOT NULL,
  `fecharegistro` DATETIME NULL,
  `idpersona` INT NULL,
  `idautomotor` INT NOT NULL,
  `fechaentrega` DATETIME NULL,
  `idempleado` INT NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  `observaciones` VARCHAR(256) NULL,
  PRIMARY KEY (`idservicio`),
  INDEX `fk_radicado_usuario1_idx` (`idusuario` ASC),
  INDEX `fk_servicio_estadoservicio1_idx` (`idestadoservicio` ASC),
  INDEX `fk_servicio_persona1_idx` (`idpersona` ASC),
  INDEX `fk_servicio_automotor1_idx` (`idautomotor` ASC),
  INDEX `fk_servicio_empleado1_idx` (`idempleado` ASC),
  CONSTRAINT `fk_radicado_usuario1`
    FOREIGN KEY (`idusuario`)
    REFERENCES `lavado`.`usuario` (`idusuario`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_servicio_estadoservicio1`
    FOREIGN KEY (`idestadoservicio`)
    REFERENCES `lavado`.`estadoservicio` (`idestadoservicio`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_servicio_persona1`
    FOREIGN KEY (`idpersona`)
    REFERENCES `lavado`.`persona` (`idpersona`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_servicio_automotor1`
    FOREIGN KEY (`idautomotor`)
    REFERENCES `lavado`.`automotor` (`idautomotor`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_servicio_empleado1`
    FOREIGN KEY (`idempleado`)
    REFERENCES `lavado`.`empleado` (`idempleado`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`rubroservicio`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`rubroservicio` (
  `idrubroservicio` INT NOT NULL AUTO_INCREMENT,
  `idservicio` INT NOT NULL,
  `idrubro` INT NOT NULL,
  `cantidad` INT NULL,
  `valor` FLOAT NULL,
  `iva` FLOAT NULL,
  `total` FLOAT NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idrubroservicio`),
  INDEX `fk_rubroservicio_servicio1_idx` (`idservicio` ASC),
  INDEX `fk_rubroservicio_rubro1_idx` (`idrubro` ASC),
  CONSTRAINT `fk_rubroservicio_servicio1`
    FOREIGN KEY (`idservicio`)
    REFERENCES `lavado`.`servicio` (`idservicio`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_rubroservicio_rubro1`
    FOREIGN KEY (`idrubro`)
    REFERENCES `lavado`.`rubro` (`idrubro`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`registrodiario`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`registrodiario` (
  `idregistrodiario` INT NOT NULL AUTO_INCREMENT,
  `idempleado` INT NOT NULL,
  `fecha` DATE NULL,
  `facturado` FLOAT NULL,
  `empleado` FLOAT NULL,
  `empleador` FLOAT NULL,
  `prestamo` FLOAT NULL,
  `otros` FLOAT NULL,
  `saldo` FLOAT NULL,
  `noentregado` FLOAT NULL,
  `entregado` VARCHAR(256) NULL,
  PRIMARY KEY (`idregistrodiario`),
  INDEX `fk_registrodiario_empleado1_idx` (`idempleado` ASC),
  CONSTRAINT `fk_registrodiario_empleado1`
    FOREIGN KEY (`idempleado`)
    REFERENCES `lavado`.`empleado` (`idempleado`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`registrosemanal`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`registrosemanal` (
  `idregistrosemanal` INT NOT NULL AUTO_INCREMENT,
  `idempleado` INT NOT NULL,
  `semana` FLOAT NULL,
  `ahorros` FLOAT NULL,
  `cobro` FLOAT NULL,
  `fecha` DATE NULL,
  PRIMARY KEY (`idregistrosemanal`),
  INDEX `fk_registrosemanal_empleado1_idx` (`idempleado` ASC),
  CONSTRAINT `fk_registrosemanal_empleado1`
    FOREIGN KEY (`idempleado`)
    REFERENCES `lavado`.`empleado` (`idempleado`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`tipogasto`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`tipogasto` (
  `idtipogasto` INT NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(256) NULL,
  `gasto` TINYINT(1) NULL,
  `modificable` TINYINT(1) NULL,
  `hash` VARCHAR(256) NULL,
  `firma` VARCHAR(1024) NULL,
  PRIMARY KEY (`idtipogasto`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lavado`.`gastodiario`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lavado`.`gastodiario` (
  `idgastodiario` INT NOT NULL AUTO_INCREMENT,
  `idtipogasto` INT NOT NULL,
  `fecha` DATE NULL,
  `valor` FLOAT NULL,
  PRIMARY KEY (`idgastodiario`),
  INDEX `fk_gastodiario_tipogasto1_idx` (`idtipogasto` ASC),
  CONSTRAINT `fk_gastodiario_tipogasto1`
    FOREIGN KEY (`idtipogasto`)
    REFERENCES `lavado`.`tipogasto` (`idtipogasto`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
