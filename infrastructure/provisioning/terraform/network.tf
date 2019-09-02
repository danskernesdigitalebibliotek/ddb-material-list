# Allocate an external ip for the cluster ingress.
resource "google_compute_address" "ingress_address" {
  name         = "ingress-address"
  address_type = "EXTERNAL"
}
output "ingress_address" {
  value = google_compute_address.ingress_address.address
}

# Setup a private network that can host communication between cloudsql and the 
# kubernetes cluster.
# Create the network.
resource "google_compute_network" "private_network" {
  provider                = "google-beta"
  auto_create_subnetworks = "true"
  name                    = "private-db-network"
}

# Configure ips.
resource "google_compute_global_address" "private_ip_address" {
  provider = "google-beta"

  name          = "private-ip-address"
  purpose       = "VPC_PEERING"
  address_type  = "INTERNAL"
  prefix_length = 20
  network       = google_compute_network.private_network.self_link
}

# Setup a connection the users of the network can access.
resource "google_service_networking_connection" "private_vpc_connection" {
  provider = "google-beta"

  network                 = google_compute_network.private_network.self_link
  service                 = "servicenetworking.googleapis.com"
  reserved_peering_ranges = [google_compute_global_address.private_ip_address.name]
}

