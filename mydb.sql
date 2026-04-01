-- =============================================================================
-- Script MySQL - Sistema de Gestión de Activos Fijos
-- Versión corregida y ajustada
-- Ajustes aplicados:
--   1. Fechas unificadas a DATETIME en todos los campos de fecha
--   2. Movimientos.UbiSecDest cambiado a INT con FK a Ubicaciones
--   3. MovimientosDet.UniActSec y LoteIngreSec permiten NULL (activo fijo vs lote)
--   4. Se agrega CHECK constraint para validar que sea activo O lote, no ambos NULL
--   5. Campos de cantidades en LotesIngreso cambiados a INT
--   6. Sucursales.SucuFecCre corregido a DATETIME
--   7. MantenimientoOrd.ManteUsuFecCrea corregido a DATETIME
--   8. ActivoFijo.UniFecIng y UniVidaYear corregidos a DATETIME e INT
--   9. Se agrega índice en Movimientos.UbiSecDest
--  10. Convención de nombres estandarizada en tabla mantenimientosTipos y mantenimientosEst
-- =============================================================================

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------------------------------
-- Schema
-- -----------------------------------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `pisciweb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pisciweb`;

-- =============================================================================
-- MÓDULO 1: CATÁLOGO DE ACTIVOS
-- =============================================================================

-- -----------------------------------------------------------------------------
-- ActivoCategoria — Nivel superior de clasificación de activos
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`ActivoCategoria` (
  `CatActiSec`  INT          NOT NULL AUTO_INCREMENT,
  `CatActNom`   VARCHAR(100) NOT NULL COMMENT 'Nombre de la categoría',
  `CatArtDesc`  VARCHAR(255) NOT NULL COMMENT 'Descripción de la categoría',
  `CatArtEst`   CHAR(1)      NOT NULL DEFAULT 'A' COMMENT 'A=Activo, I=Inactivo',
  PRIMARY KEY (`CatActiSec`)
) ENGINE = InnoDB COMMENT = 'Categorías de activos (ej: Tecnología, Muebles, Vehículos)';


-- -----------------------------------------------------------------------------
-- ActivoTipo — Tipo dentro de una categoría
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`ActivoTipo` (
  `TipActSec`  INT         NOT NULL AUTO_INCREMENT,
  `CatActiSec` INT         NOT NULL COMMENT 'FK Categoría padre',
  `TipActNom`  VARCHAR(100) NOT NULL COMMENT 'Nombre del tipo',
  `TipActEst`  CHAR(1)     NOT NULL DEFAULT 'A' COMMENT 'A=Activo, I=Inactivo',
  PRIMARY KEY (`TipActSec`),
  INDEX `fk_ActivoTipo_ActivoCategoria_idx` (`CatActiSec` ASC),
  CONSTRAINT `fk_ActivoTipo_ActivoCategoria`
    FOREIGN KEY (`CatActiSec`) REFERENCES `pisciweb`.`ActivoCategoria` (`CatActiSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB COMMENT = 'Tipos de activo dentro de cada categoría';


-- -----------------------------------------------------------------------------
-- Marcas
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`Marcas` (
  `MarcasSec` INT          NOT NULL AUTO_INCREMENT,
  `MarcasNom` VARCHAR(200) NOT NULL COMMENT 'Nombre de la marca',
  `MarcasEst` CHAR(1)      NOT NULL DEFAULT 'A' COMMENT 'A=Activo, I=Inactivo',
  PRIMARY KEY (`MarcasSec`)
) ENGINE = InnoDB COMMENT = 'Marcas o fabricantes de activos';


-- -----------------------------------------------------------------------------
-- ControlInventario — Define la modalidad de control (serial, lote, consumible)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`ControlInventario` (
  `ContInvSec` INT         NOT NULL,
  `ConInvNom`  VARCHAR(100) NOT NULL COMMENT 'Ej: Por serial, Por lote, Consumible',
  PRIMARY KEY (`ContInvSec`)
) ENGINE = InnoDB COMMENT = 'Modalidades de control de inventario por modelo';


-- -----------------------------------------------------------------------------
-- ActivosModelos — Modelo/referencia de activo (no la unidad física)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`ActivosModelos` (
  `ModelActSec`     INT          NOT NULL AUTO_INCREMENT,
  `TipActSec`       INT          NOT NULL COMMENT 'FK Tipo de activo',
  `ContInvSec`      INT          NOT NULL COMMENT 'FK Control de inventario',
  `MarcasSec`       INT          NOT NULL COMMENT 'FK Marca',
  `ModelActNom`     VARCHAR(100) NOT NULL COMMENT 'Nombre del modelo',
  `ModelActDesc`    VARCHAR(255) NOT NULL COMMENT 'Descripción del modelo',
  `ModelActEspGen`  VARCHAR(500) NULL     COMMENT 'Especificaciones técnicas generales',
  `ModelActEst`     CHAR(1)      NOT NULL DEFAULT 'A' COMMENT 'A=Activo, I=Inactivo',
  `ModelCheckMant`  CHAR(1)      NULL     COMMENT 'S=Requiere mantenimiento',
  `ModelCheckComp`  CHAR(1)      NULL     COMMENT 'S=Es componente de otro activo',
  `ModelCheckReqPlac` CHAR(1)    NULL     COMMENT 'S=Requiere placa de identificación',
  `ModalFichAdj`    VARCHAR(255) NULL     COMMENT 'URL ficha técnica adjunta',
  PRIMARY KEY (`ModelActSec`),
  INDEX `fk_ActivosModelos_ActivoTipo_idx`          (`TipActSec`  ASC),
  INDEX `fk_ActivosModelos_Marcas_idx`              (`MarcasSec`  ASC),
  INDEX `fk_ActivosModelos_ControlInventario_idx`   (`ContInvSec` ASC),
  CONSTRAINT `fk_ActivosModelos_ActivoTipo`
    FOREIGN KEY (`TipActSec`)  REFERENCES `pisciweb`.`ActivoTipo`         (`TipActSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ActivosModelos_Marcas`
    FOREIGN KEY (`MarcasSec`)  REFERENCES `pisciweb`.`Marcas`             (`MarcasSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ActivosModelos_ControlInventario`
    FOREIGN KEY (`ContInvSec`) REFERENCES `pisciweb`.`ControlInventario`  (`ContInvSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB COMMENT = 'Catálogo de modelos/referencias de activos';


-- =============================================================================
-- MÓDULO 2: GEOGRAFÍA Y UBICACIONES
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Empresa
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`Empresa` (
  `EmpreSec`     INT          NOT NULL AUTO_INCREMENT,
  `EmpreNom`     VARCHAR(200) NOT NULL COMMENT 'Razón social',
  `EmpreNit`     VARCHAR(20)  NOT NULL COMMENT 'NIT o identificación tributaria',
  `EmpreEst`     CHAR(1)      NOT NULL DEFAULT 'A' COMMENT 'A=Activo, I=Inactivo',
  `EmpreFecCrea` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `EmpreCodSiigo` VARCHAR(45) NULL     COMMENT 'Código en Siigo',
  `EmpreCodTech`  VARCHAR(45) NULL     COMMENT 'Código en sistema tecnológico',
  PRIMARY KEY (`EmpreSec`)
) ENGINE = InnoDB COMMENT = 'Empresas propietarias de los activos';


-- -----------------------------------------------------------------------------
-- Sucursales
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`Sucursales` (
  `SucuSec`     INT          NOT NULL AUTO_INCREMENT,
  `EmpreSec`    INT          NOT NULL COMMENT 'FK Empresa',
  `SucuNom`     VARCHAR(200) NOT NULL COMMENT 'Nombre de la sucursal',
  `SucuEst`     CHAR(1)      NOT NULL DEFAULT 'A' COMMENT 'A=Activo, I=Inactivo',
  `SucuFecCre`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'CORREGIDO: era VARCHAR',
  `SucuLatitud`  VARCHAR(50) NULL     COMMENT 'Coordenada latitud',
  `SucuLongitud` VARCHAR(50) NULL     COMMENT 'Coordenada longitud',
  `SucCodSiigo`  VARCHAR(45) NULL,
  `SucCodTech`   VARCHAR(45) NULL,
  PRIMARY KEY (`SucuSec`),
  INDEX `fk_Sucursales_Empresa_idx` (`EmpreSec` ASC),
  CONSTRAINT `fk_Sucursales_Empresa`
    FOREIGN KEY (`EmpreSec`) REFERENCES `pisciweb`.`Empresa` (`EmpreSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB COMMENT = 'Sucursales o sedes de cada empresa';


-- -----------------------------------------------------------------------------
-- UbicacionesTipo — Tipo de ubicación (bodega, oficina, piso, etc.)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`UbicacionesTipo` (
  `UbiTipSec`    INT          NOT NULL AUTO_INCREMENT,
  `UbiTipNom`    VARCHAR(100) NOT NULL COMMENT 'Ej: Bodega, Oficina, Sala de servidores',
  `UbiTipFecCrea` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UbiTipUsuCrea` VARCHAR(100) NOT NULL COMMENT 'Usuario que creó el registro',
  `UbiTipEst`    CHAR(1)      NOT NULL DEFAULT 'A' COMMENT 'A=Activo, I=Inactivo',
  PRIMARY KEY (`UbiTipSec`)
) ENGINE = InnoDB COMMENT = 'Tipos de ubicación física';


-- -----------------------------------------------------------------------------
-- Ubicaciones
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`Ubicaciones` (
  `UbicaSec`    INT          NOT NULL AUTO_INCREMENT,
  `SucuSec`     INT          NOT NULL COMMENT 'FK Sucursal',
  `UbiTipSec`   INT          NOT NULL COMMENT 'FK Tipo de ubicación',
  `UbicaNom`    VARCHAR(100) NOT NULL COMMENT 'Nombre de la ubicación',
  `UbicaEst`    CHAR(1)      NOT NULL DEFAULT 'A' COMMENT 'A=Activo, I=Inactivo',
  `UbicaFecCrea` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UbicaFecMod`  DATETIME    NULL     COMMENT 'Fecha última modificación',
  `UbicaCodSiigo` VARCHAR(45) NULL,
  `UbicaCodTech`  VARCHAR(45) NULL,
  `UbicaLongitud` VARCHAR(50) NULL,
  `UbicaLatitud`  VARCHAR(50) NULL,
  PRIMARY KEY (`UbicaSec`),
  INDEX `fk_Ubicaciones_UbicacionesTipo_idx` (`UbiTipSec` ASC),
  INDEX `fk_Ubicaciones_Sucursales_idx`      (`SucuSec`   ASC),
  CONSTRAINT `fk_Ubicaciones_UbicacionesTipo`
    FOREIGN KEY (`UbiTipSec`) REFERENCES `pisciweb`.`UbicacionesTipo` (`UbiTipSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Ubicaciones_Sucursales`
    FOREIGN KEY (`SucuSec`)   REFERENCES `pisciweb`.`Sucursales`       (`SucuSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB COMMENT = 'Ubicaciones físicas dentro de cada sucursal';


-- =============================================================================
-- MÓDULO 3: ACTIVO FIJO (UNIDAD FÍSICA)
-- =============================================================================

-- -----------------------------------------------------------------------------
-- ActivoEstado — Estados posibles de un activo (Activo, En mantenimiento, Dado de baja, etc.)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`ActivoEstado` (
  `EstActSec` INT         NOT NULL AUTO_INCREMENT,
  `EstActNom` VARCHAR(100) NOT NULL COMMENT 'Ej: Activo, En mantenimiento, Dado de baja',
  PRIMARY KEY (`EstActSec`)
) ENGINE = InnoDB COMMENT = 'Catálogo de estados de activos fijos';


-- -----------------------------------------------------------------------------
-- ActivoFijo — Unidad física individual de un activo
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`ActivoFijo` (
  `UniActSec`       INT          NOT NULL AUTO_INCREMENT,
  `ModelActSec`     INT          NOT NULL COMMENT 'FK Modelo del activo',
  `UniSerial`       VARCHAR(100) NOT NULL COMMENT 'Número de serie',
  `UniPlaca`        VARCHAR(45)  NULL     COMMENT 'Placa de inventario (si aplica según modelo)',
  `UniFecIng`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'CORREGIDO: era VARCHAR',
  `UniVidaYear`     INT          NULL     COMMENT 'Vida útil estimada en años. CORREGIDO: era VARCHAR',
  `EstActSec`       INT          NOT NULL COMMENT 'FK Estado actual del activo',
  `UbicaSec`        INT          NULL     COMMENT 'FK Ubicación actual (NULL si no asignado)',
  `ActiFecUltMant`  DATETIME     NULL     COMMENT 'Fecha del último mantenimiento realizado',
  PRIMARY KEY (`UniActSec`),
  INDEX `fk_ActivoFijo_ActivosModelos_idx`  (`ModelActSec` ASC),
  INDEX `fk_ActivoFijo_ActivoEstado_idx`    (`EstActSec`   ASC),
  INDEX `fk_ActivoFijo_Ubicaciones_idx`     (`UbicaSec`    ASC),
  CONSTRAINT `fk_ActivoFijo_ActivosModelos`
    FOREIGN KEY (`ModelActSec`) REFERENCES `pisciweb`.`ActivosModelos` (`ModelActSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ActivoFijo_ActivoEstado`
    FOREIGN KEY (`EstActSec`)   REFERENCES `pisciweb`.`ActivoEstado`   (`EstActSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ActivoFijo_Ubicaciones`
    FOREIGN KEY (`UbicaSec`)    REFERENCES `pisciweb`.`Ubicaciones`    (`UbicaSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB COMMENT = 'Unidades físicas individuales de activos fijos';


-- =============================================================================
-- MÓDULO 4: USUARIOS Y SEGURIDAD
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Perfil
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`Perfil` (
  `PerSec` INT         NOT NULL AUTO_INCREMENT,
  `PerNom` VARCHAR(100) NOT NULL COMMENT 'Nombre del perfil (Administrador, Técnico, etc.)',
  `PerEst` CHAR(1)     NOT NULL DEFAULT 'A' COMMENT 'A=Activo, I=Inactivo',
  PRIMARY KEY (`PerSec`)
) ENGINE = InnoDB COMMENT = 'Perfiles de acceso al sistema';


-- -----------------------------------------------------------------------------
-- Usuario
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`Usuario` (
  `UsuSec`     INT          NOT NULL AUTO_INCREMENT,
  `PerSec`     INT          NOT NULL COMMENT 'FK Perfil de acceso',
  `UsuCod`     VARCHAR(100) NOT NULL UNIQUE COMMENT 'Código único de usuario',
  `UsuNom`     VARCHAR(250) NOT NULL COMMENT 'Nombre completo',
  `UsuPass`    VARCHAR(255) NOT NULL COMMENT 'Hash de contraseña (no texto plano)',
  `UsuEst`     CHAR(1)      NOT NULL DEFAULT 'A' COMMENT 'A=Activo, I=Inactivo',
  `UsuCel`     VARCHAR(15)  NOT NULL COMMENT 'Número celular',
  `UsuEmail`   VARCHAR(150) NOT NULL UNIQUE COMMENT 'Correo electrónico',
  `UsuFecCrea` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UsuUsuCrea` VARCHAR(100) NOT NULL COMMENT 'Usuario que creó el registro',
  `UsuLastIng` DATETIME     NULL     COMMENT 'Último ingreso al sistema',
  PRIMARY KEY (`UsuSec`),
  INDEX `fk_Usuario_Perfil_idx` (`PerSec` ASC),
  CONSTRAINT `fk_Usuario_Perfil`
    FOREIGN KEY (`PerSec`) REFERENCES `pisciweb`.`Perfil` (`PerSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB COMMENT = 'Usuarios del sistema';


-- =============================================================================
-- MÓDULO 5: MOVIMIENTOS E INVENTARIO
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Fuentes — Origen o tipo de transacción (Ingreso, Salida, Traslado, Baja)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`Fuentes` (
  `FueCod`           INT         NOT NULL AUTO_INCREMENT,
  `FueNom`           VARCHAR(100) NOT NULL COMMENT 'Ej: Ingreso compra, Traslado, Baja',
  `FueAction`        CHAR(1)     NOT NULL COMMENT 'E=Entrada, S=Salida, T=Traslado',
  `FueCheckAfectInv` CHAR(1)     NULL     COMMENT 'S=Afecta inventario contable',
  PRIMARY KEY (`FueCod`)
) ENGINE = InnoDB COMMENT = 'Tipos de fuente/origen de movimientos';


-- -----------------------------------------------------------------------------
-- LotesIngreso — Lotes para activos controlados por cantidad (consumibles)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`LotesIngreso` (
  `LoteIngreSec` INT          NOT NULL AUTO_INCREMENT,
  `ModelActSec`  INT          NOT NULL COMMENT 'FK Modelo al que pertenece el lote',
  `LoteCod`      VARCHAR(45)  NOT NULL COMMENT 'Código del lote',
  `LoteCantIni`  INT          NOT NULL COMMENT 'Cantidad inicial al ingresar. CORREGIDO: era VARCHAR',
  `LoteCantAct`  INT          NOT NULL COMMENT 'Cantidad actual disponible. CORREGIDO: era VARCHAR',
  `LotCost`      DECIMAL(20,2) NULL    COMMENT 'Costo unitario del lote',
  `LotFecIng`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de ingreso',
  `LotEst`       CHAR(1)      NOT NULL DEFAULT 'A' COMMENT 'A=Activo, C=Consumido, I=Inactivo',
  `LotUsuCrea`   VARCHAR(100) NOT NULL COMMENT 'Usuario que registró el lote',
  PRIMARY KEY (`LoteIngreSec`),
  INDEX `fk_LotesIngreso_ActivosModelos_idx` (`ModelActSec` ASC),
  CONSTRAINT `fk_LotesIngreso_ActivosModelos`
    FOREIGN KEY (`ModelActSec`) REFERENCES `pisciweb`.`ActivosModelos` (`ModelActSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB COMMENT = 'Lotes de ingreso para activos controlados por cantidad';


-- -----------------------------------------------------------------------------
-- Movimientos — Cabecera de cada transacción de inventario
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`Movimientos` (
  `MoviSec`    INT          NOT NULL AUTO_INCREMENT,
  `MoviNro`    VARCHAR(45)  NOT NULL UNIQUE COMMENT 'Número consecutivo del movimiento',
  `MoviFecCre` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UsuSec`     INT          NOT NULL COMMENT 'FK Usuario que registra',
  `FueCod`     INT          NOT NULL COMMENT 'FK Fuente/tipo de movimiento',
  `UbicaSec`   INT          NOT NULL COMMENT 'FK Ubicación origen',
  `UbiSecDest` INT          NULL     COMMENT 'FK Ubicación destino (CORREGIDO: era VARCHAR, ahora INT con FK)',
  `MoviEst`    CHAR(1)      NOT NULL DEFAULT 'P' COMMENT 'P=Pendiente, A=Aprobado, X=Anulado',
  `MoviSecAlt` VARCHAR(45)  NULL     COMMENT 'Referencia a documento alterno (factura, etc.)',
  PRIMARY KEY (`MoviSec`),
  INDEX `fk_Movimientos_Fuentes_idx`      (`FueCod`     ASC),
  INDEX `fk_Movimientos_Ubicaciones_idx`  (`UbicaSec`   ASC),
  INDEX `fk_Movimientos_UbicDest_idx`     (`UbiSecDest` ASC),
  INDEX `fk_Movimientos_Usuario_idx`      (`UsuSec`     ASC),
  CONSTRAINT `fk_Movimientos_Fuentes`
    FOREIGN KEY (`FueCod`)     REFERENCES `pisciweb`.`Fuentes`     (`FueCod`)
    ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Movimientos_Ubicaciones`
    FOREIGN KEY (`UbicaSec`)   REFERENCES `pisciweb`.`Ubicaciones` (`UbicaSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Movimientos_UbicDest`
    FOREIGN KEY (`UbiSecDest`) REFERENCES `pisciweb`.`Ubicaciones` (`UbicaSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Movimientos_Usuario`
    FOREIGN KEY (`UsuSec`)     REFERENCES `pisciweb`.`Usuario`     (`UsuSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB COMMENT = 'Cabecera de movimientos de inventario y traslados';


-- -----------------------------------------------------------------------------
-- MovimientosDet — Detalle de cada movimiento
-- AJUSTE: UniActSec y LoteIngreSec ahora son NULL opcionales.
--         Un renglón es de activo fijo individual OR de lote, no ambos.
--         El CHECK garantiza que al menos uno esté presente.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`MovimientosDet` (
  `MoviDetSec`   INT      NOT NULL AUTO_INCREMENT,
  `MoviSec`      INT      NOT NULL COMMENT 'FK Movimiento cabecera',
  `ModelActSec`  INT      NOT NULL COMMENT 'FK Modelo del activo',
  `UniActSec`    INT      NULL     COMMENT 'FK Activo fijo individual (NULL si es por lote)',
  `LoteIngreSec` INT      NULL     COMMENT 'FK Lote (NULL si es activo fijo individual)',
  `MoviCant`     INT      NOT NULL DEFAULT 1 COMMENT 'Cantidad movida',
  `MoviSecMov`   INT      NOT NULL COMMENT 'Secuencia del renglón dentro del movimiento',
  `Movisigno`    SMALLINT NOT NULL COMMENT '1=Entrada, -1=Salida',
  PRIMARY KEY (`MoviDetSec`),
  INDEX `fk_MovimientosDet_Movimientos_idx`    (`MoviSec`      ASC),
  INDEX `fk_MovimientosDet_ActivosModelos_idx` (`ModelActSec`  ASC),
  INDEX `fk_MovimientosDet_ActivoFijo_idx`     (`UniActSec`    ASC),
  INDEX `fk_MovimientosDet_LotesIngreso_idx`   (`LoteIngreSec` ASC),
  CONSTRAINT `fk_MovimientosDet_Movimientos`
    FOREIGN KEY (`MoviSec`)      REFERENCES `pisciweb`.`Movimientos`    (`MoviSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_MovimientosDet_ActivosModelos`
    FOREIGN KEY (`ModelActSec`)  REFERENCES `pisciweb`.`ActivosModelos` (`ModelActSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_MovimientosDet_ActivoFijo`
    FOREIGN KEY (`UniActSec`)    REFERENCES `pisciweb`.`ActivoFijo`     (`UniActSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_MovimientosDet_LotesIngreso`
    FOREIGN KEY (`LoteIngreSec`) REFERENCES `pisciweb`.`LotesIngreso`   (`LoteIngreSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `chk_MoviDet_tipo`
    CHECK (
      (`UniActSec` IS NOT NULL AND `LoteIngreSec` IS NULL)
      OR
      (`UniActSec` IS NULL AND `LoteIngreSec` IS NOT NULL)
    )
) ENGINE = InnoDB COMMENT = 'Detalle de ítems por movimiento. Un renglón = activo fijo O lote, nunca ambos';


-- =============================================================================
-- MÓDULO 6: MANTENIMIENTO
-- =============================================================================

-- -----------------------------------------------------------------------------
-- MantenimientosTipos — Correctivo, Preventivo, etc.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`MantenimientosTipos` (
  `ManteTipSec` INT         NOT NULL AUTO_INCREMENT,
  `ManteTipNom` VARCHAR(100) NOT NULL COMMENT 'Ej: Preventivo, Correctivo, Predictivo',
  `ManteTipEst` CHAR(1)     NOT NULL DEFAULT 'A' COMMENT 'A=Activo, I=Inactivo',
  PRIMARY KEY (`ManteTipSec`)
) ENGINE = InnoDB COMMENT = 'Tipos de mantenimiento';


-- -----------------------------------------------------------------------------
-- MantenimientosEst — Estados de la orden (Abierta, En proceso, Cerrada)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`MantenimientosEst` (
  `ManteEstSec` INT         NOT NULL AUTO_INCREMENT,
  `ManteEstNom` VARCHAR(100) NOT NULL COMMENT 'Ej: Abierta, En proceso, Cerrada, Cancelada',
  PRIMARY KEY (`ManteEstSec`)
) ENGINE = InnoDB COMMENT = 'Estados de órdenes de mantenimiento';


-- -----------------------------------------------------------------------------
-- MantenimientoOrd — Orden de mantenimiento (cabecera)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`MantenimientoOrd` (
  `MantSec`          INT           NOT NULL AUTO_INCREMENT,
  `MantNro`          VARCHAR(45)   NOT NULL UNIQUE COMMENT 'Número de la orden',
  `ManteTipSec`      INT           NOT NULL COMMENT 'FK Tipo de mantenimiento',
  `MantePrio`        CHAR(1)       NOT NULL COMMENT 'A=Alta, M=Media, B=Baja',
  `ManteDesc`        VARCHAR(500)  NOT NULL COMMENT 'Descripción del trabajo a realizar',
  `ManteusuCrea`     VARCHAR(100)  NOT NULL COMMENT 'Usuario que generó la orden',
  `ManteUsuFecCrea`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'CORREGIDO: era VARCHAR',
  `ManteEstSec`      INT           NOT NULL COMMENT 'FK Estado actual de la orden',
  `ManteValManObra`  DECIMAL(12,2) NULL     COMMENT 'Valor de mano de obra',
  `ManteObsDiag`     VARCHAR(500)  NULL     COMMENT 'Observaciones del diagnóstico',
  `ManteObsFinal`    VARCHAR(500)  NULL     COMMENT 'Observaciones al cierre',
  `ManteUsuRes`      VARCHAR(100)  NULL     COMMENT 'Usuario responsable de ejecutar',
  `ManteFecMod`      DATETIME      NULL     COMMENT 'Fecha de última modificación',
  PRIMARY KEY (`MantSec`),
  INDEX `fk_MantenimientoOrd_Tipos_idx` (`ManteTipSec` ASC),
  INDEX `fk_MantenimientoOrd_Est_idx`   (`ManteEstSec` ASC),
  CONSTRAINT `fk_MantenimientoOrd_Tipos`
    FOREIGN KEY (`ManteTipSec`) REFERENCES `pisciweb`.`MantenimientosTipos` (`ManteTipSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_MantenimientoOrd_Est`
    FOREIGN KEY (`ManteEstSec`) REFERENCES `pisciweb`.`MantenimientosEst`   (`ManteEstSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB COMMENT = 'Órdenes de trabajo de mantenimiento';


-- -----------------------------------------------------------------------------
-- MantenimientoDet — Activos intervenidos por orden
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`MantenimientoDet` (
  `MantDet`               INT          NOT NULL AUTO_INCREMENT,
  `MantSec`               INT          NOT NULL COMMENT 'FK Orden de mantenimiento',
  `UniActSec`             INT          NOT NULL COMMENT 'FK Activo intervenido',
  `MantDetEst`            CHAR(1)      NOT NULL COMMENT 'P=Pendiente, E=En proceso, F=Finalizado',
  `MantDetDescTrabajo`    VARCHAR(500) NULL     COMMENT 'Descripción del trabajo realizado',
  `MantDetLecturaMedidor` VARCHAR(100) NULL     COMMENT 'Lectura de odómetro/horometro si aplica',
  `MantDetObservacion`    VARCHAR(500) NULL     COMMENT 'Observaciones del técnico',
  `MantDetResponsable`    VARCHAR(100) NULL     COMMENT 'Técnico responsable del ítem',
  PRIMARY KEY (`MantDet`),
  INDEX `fk_MantenimientoDet_Ord_idx`  (`MantSec`   ASC),
  INDEX `fk_MantenimientoDet_Activo_idx` (`UniActSec` ASC),
  CONSTRAINT `fk_MantenimientoDet_Ord`
    FOREIGN KEY (`MantSec`)   REFERENCES `pisciweb`.`MantenimientoOrd` (`MantSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_MantenimientoDet_Activo`
    FOREIGN KEY (`UniActSec`) REFERENCES `pisciweb`.`ActivoFijo`       (`UniActSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB COMMENT = 'Detalle de activos por orden de mantenimiento';


-- -----------------------------------------------------------------------------
-- MantenimientoEvidencia — Fotos, documentos y evidencias adjuntas
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pisciweb`.`MantenimientoEvidencia` (
  `MantEvSec`   INT          NOT NULL AUTO_INCREMENT,
  `Ord_MantSec` INT          NOT NULL COMMENT 'FK Orden de mantenimiento',
  `MantEvUrl`   VARCHAR(500) NOT NULL COMMENT 'URL o ruta del archivo adjunto',
  `MantEvTipo`  VARCHAR(45)  NOT NULL COMMENT 'Ej: imagen, pdf, video',
  `MantEvDesc`  VARCHAR(255) NOT NULL COMMENT 'Descripción de la evidencia',
  `MantEvFecha` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'CORREGIDO: era VARCHAR',
  PRIMARY KEY (`MantEvSec`),
  INDEX `fk_MantenimientoEv_Ord_idx` (`Ord_MantSec` ASC),
  CONSTRAINT `fk_MantenimientoEv_Ord`
    FOREIGN KEY (`Ord_MantSec`) REFERENCES `pisciweb`.`MantenimientoOrd` (`MantSec`)
    ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB COMMENT = 'Evidencias fotográficas y documentales de mantenimientos';


-- =============================================================================
-- DATOS INICIALES (catálogos mínimos para operar)
-- =============================================================================

INSERT INTO `pisciweb`.`ControlInventario` (`ContInvSec`, `ConInvNom`) VALUES
  (1, 'Por serial (activo fijo individual)'),
  (2, 'Por lote (consumibles y suministros)'),
  (3, 'Por cantidad sin serial');

INSERT INTO `pisciweb`.`ActivoEstado` (`EstActNom`) VALUES
  ('Activo'),
  ('En mantenimiento'),
  ('Dado de baja'),
  ('En traslado'),
  ('Perdido / robado');

INSERT INTO `pisciweb`.`MantenimientosTipos` (`ManteTipNom`, `ManteTipEst`) VALUES
  ('Preventivo', 'A'),
  ('Correctivo', 'A'),
  ('Predictivo', 'A'),
  ('Metrología', 'A');

INSERT INTO `pisciweb`.`MantenimientosEst` (`ManteEstNom`) VALUES
  ('Abierta'),
  ('En proceso'),
  ('Cerrada'),
  ('Cancelada');

INSERT INTO `pisciweb`.`Fuentes` (`FueNom`, `FueAction`, `FueCheckAfectInv`) VALUES
  ('Ingreso por compra',    'E', 'S'),
  ('Ingreso por donación',  'E', 'S'),
  ('Salida por baja',       'S', 'S'),
  ('Traslado entre sedes',  'T', 'S'),
  ('Préstamo temporal',     'T', 'N'),
  ('Devolución préstamo',   'E', 'N');


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
