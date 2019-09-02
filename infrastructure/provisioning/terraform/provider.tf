# Setup access to google cloud.
# Credentials are assumed to be available via a 
provider "google" {
  version = "~> 2.12"
  region  = var.region
  project = var.project_id
}

provider "google-beta" {
  version = "~> 2.12"
  region  = var.region
  project = var.project_id
}
