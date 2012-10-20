SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';


-- -----------------------------------------------------
-- Table `account_users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `account_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `username` VARCHAR(45) NOT NULL ,
  `password` VARCHAR(128) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `username_UNIQUE` (`username` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `account_tokens`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `account_tokens` (
  `token` VARCHAR(40) NOT NULL ,
  `user_id` INT(11) UNSIGNED NOT NULL ,
  `user_agent` VARCHAR(40) NOT NULL ,
  `expires` INT(10) NULL ,
  PRIMARY KEY (`token`, `user_id`) ,
  UNIQUE INDEX `token_UNIQUE` (`token` ASC) ,
  INDEX `fk_account_tokens_account_users1` (`user_id` ASC) ,
  CONSTRAINT `fk_account_tokens_account_users1`
    FOREIGN KEY (`user_id` )
    REFERENCES `account_users` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
