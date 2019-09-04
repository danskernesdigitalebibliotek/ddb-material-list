
terraform {
  backend "gcs" {
    bucket = "reops-oc-state-110242"
    prefix = "state/reload-material-list"
  }
}
