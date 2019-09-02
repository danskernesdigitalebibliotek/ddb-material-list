variable "project_name" {
  description = "Project name"
}

variable "project_id" {
  description = "unique project id"
}

variable "billing_account" {
  description = "ID for the billing-account associated with the project"
}
variable "organization_id" {
  description = "ID of the organization that hosts the project"
}
variable "region" {
  description = "Region the project-resources should be created in"
  default     = "europe-west1"
}
variable "db_instance_type" {
  description = "The vm instance type used for hosting cloudsql"
}
variable "zone_1" {
  description = "The primary zone used to host the cluster nodes and primary cloudsql pr. default"
  default     = "europe-west1-b"
}
variable "zone_2" {
  description = "The secondary zone that will hold the cloudsql read replica."
  default     = "europe-west1-c"
}

variable "cluster_node_service_account_id" {
  description = "The Id of the service-account the gke cluster nodes will run as"
  default     = "gke-cluster-node"
}

variable "deployer_service_account_id" {
  description = "The Id of the service-account that will be used for deployment"
  default     = "deployer"
}

variable "pool_preemptible_name" {
  description = "Name of the preemptible node pool"
  default     = "preempt-2cpu-7500"
}

variable "pool_preemptible_node_count" {
  description = "Number of nodes in the preemptible node-pool"
  type        = number
  default     = 2

}
variable "pool_preemptible_machine_type" {
  description = "The machine-type used for the node-pool"
  default     = "n1-standard-2"
}
