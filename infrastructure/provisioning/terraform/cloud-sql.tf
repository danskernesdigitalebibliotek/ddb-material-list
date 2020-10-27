resource "google_sql_database_instance" "master" {
  provider = google-beta

  name   = "material-list-alpha2"
  region = var.region

  database_version = "MYSQL_5_7"

  depends_on = [google_service_networking_connection.private_vpc_connection]
  settings {
    # Second-generation instance tiers are based on the machine
    # type. See argument reference below.
    tier            = var.db_instance_type
    disk_autoresize = true
    disk_size       = 50
    ip_configuration {
      ipv4_enabled    = false
      private_network = google_compute_network.private_network.self_link
    }

    backup_configuration {
      binary_log_enabled = "true"
      enabled            = "true"
      start_time         = "03:04"
    }

    location_preference {
      zone = var.zone_1
    }
  }
}

resource "google_sql_database_instance" "replica" {
  name   = "material-list-beta2"
  region = var.region

  database_version     = "MYSQL_5_7"
  master_instance_name = google_sql_database_instance.master.name

  replica_configuration {
    connect_retry_interval = "30"
    failover_target        = "true"
  }

  settings {
    tier            = var.db_instance_type
    disk_autoresize = true
    disk_size       = 50
    ip_configuration {
      ipv4_enabled    = false
      private_network = google_compute_network.private_network.self_link
    }

    location_preference {
      zone = var.zone_2
    }

  }
}

resource "google_sql_database" "ml_prod" {
  name      = "ml_prod"
  instance  = google_sql_database_instance.master.name
  charset   = "utf8mb4"
  collation = "utf8mb4_general_ci"
}

resource "google_sql_database" "ml_test" {
  name      = "ml_test"
  instance  = google_sql_database_instance.master.name
  charset   = "utf8mb4"
  collation = "utf8mb4_general_ci"
}

resource "google_sql_user" "ml_test" {
  name     = "ml_test_user"
  instance = google_sql_database_instance.master.name
  password = "ahs2faaFee"
}

output "master_private_ip" {
  value = google_sql_database_instance.master.private_ip_address
}

output "master_public_ip" {
  value       = google_sql_database_instance.master.ip_address.0.ip_address
  description = "The IPv4 address assigned for master"
}

output "test_sql_user_username" {
  value       = google_sql_user.ml_test.name
  description = "Username for the cloud sql test user"
}

output "test_sql_user_password" {
  value       = google_sql_user.ml_test.password
  description = "Password for the cloud sql test user"
}


