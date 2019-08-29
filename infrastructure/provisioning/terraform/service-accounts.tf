# Create a service-account for the cluster.
resource "google_service_account" "cluster-node" {
  account_id   = "${var.cluster_node_service_account_id}"
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
