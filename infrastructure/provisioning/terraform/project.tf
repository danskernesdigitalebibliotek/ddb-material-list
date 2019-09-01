# Configure a project with a deprivileged serviceaccount.
module "project-factory" {
  source                  = "terraform-google-modules/project-factory/google"
  version                 = "~> 3.2"
  project_id              = "${var.project_id}"
  name                    = "${var.project_name}"
  org_id                  = "${var.organization_id}"
  billing_account         = "${var.billing_account}"
  default_service_account = "depriviledge"
  lien                    = "true"
  credentials_path        = "/tmp/credentials.json"
  activate_apis = [
    "cloudbuild.googleapis.com",
    "cloudresourcemanager.googleapis.com",
    "container.googleapis.com",
    "compute.googleapis.com",
    "logging.googleapis.com",
    "servicenetworking.googleapis.com",
    "sql-component.googleapis.com",
    "sqladmin.googleapis.com"
  ]
}

# Grant reload developers editor access to the project, k8s and storage.
resource "google_project_iam_member" "grant-developer-editor" {
  project = module.project-factory.project_id
  role    = "roles/editor"
  member  = "group:developers@reload.dk"
}
resource "google_project_iam_member" "grant-developer-gke" {
  project = module.project-factory.project_id
  role    = "roles/container.admin"
  member  = "group:developers@reload.dk"
}
resource "google_project_iam_member" "grant-developer-storage" {
  project = module.project-factory.project_id
  role    = "roles/storage.admin"
  member  = "group:developers@reload.dk"
}
