drop table if exists virgo_product;
create table virgo_product
(
    id          bigint unsigned primary key auto_increment,
    sku         varchar(50),
    size        varchar(50),
    image       varchar(500),
    last        varchar(500),
    material_1  varchar(500),
    material_2  varchar(500),
    platform    varchar(500),
    heel        varchar(500),
    bottom_1    varchar(500),
    bottom_2    varchar(500),
    bottom_3    varchar(500),
    accessory_1 varchar(500),
    accessory_2 varchar(500),
    price_1     int unsigned default 0 comment 'in cent',
    price_2     int unsigned default 0 comment 'in cent',
    template    varchar(500),
    process_1   varchar(500),
    process_2   varchar(500),
    third_party varchar(500),
    update_date date,
    index(sku),
    index(size),
    index(update_date)
) engine = InnoDB
  default charset = utf8mb4
  collate = utf8mb4_unicode_ci;

drop trigger if exists tri_virgo_product;
create trigger tri_virgo_product before update on virgo_product for each row set new.update_date=current_date;
