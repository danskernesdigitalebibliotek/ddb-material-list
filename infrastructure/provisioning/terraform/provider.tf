# Setup access to google cloud.
# Credentials are assumed to be available via a file pointed to by the
# GOOGLE_CREDENTIALS environment variable.
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
