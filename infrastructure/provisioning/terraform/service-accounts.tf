# Create a service-account for deployments.
resource "google_service_account" "deployer" {
  account_id   = var.deployer_service_account_id
  display_name = "GKE Cluster Node Service Account"
}
# Grant deployer developer access to the cluster.
resource "google_project_iam_member" "deployer-cluster-developer" {
  role   = "roles/container.developer"
  member = "serviceAccount:${google_service_account.deployer.email}"
}

# Grant deployer developer access push til gcr.
# Allow anyone to view the repo bucket
resource "google_storage_bucket_iam_member" "deployer-write" {
  bucket = google_storage_bucket.gcr.name
  role   = "roles/storage.admin"
  member = "serviceAccount:${google_service_account.deployer.email}"
}

# Create a service-account for the cluster.
resource "google_service_account" "cluster-node" {
  account_id   = var.cluster_node_service_account_id
  display_name = "GKE Cluster Node Service Account"
}

# Grant the cluster service-account access to write logs, read and write metrics,
# and pull images from google-storage.
resource "google_project_iam_member" "cluster-node-logwriter" {
  role   = "roles/logging.logWriter"
  member = "serviceAccount:${google_service_account.cluster-node.email}"
}
resource "google_project_iam_member" "cluster-node-metric-writer" {
  role   = "roles/monitoring.metricWriter"
  member = "serviceAccount:${google_service_account.cluster-node.email}"
}
resource "google_project_iam_member" "cluster-node-monitoring-viewer" {
  role   = "roles/monitoring.viewer"
  member = "serviceAccount:${google_service_account.cluster-node.email}"
}
resource "google_project_iam_member" "cluster-node-storage-viewer" {
  role   = "roles/storage.objectViewer"
  member = "serviceAccount:${google_service_account.cluster-node.email}"
}
