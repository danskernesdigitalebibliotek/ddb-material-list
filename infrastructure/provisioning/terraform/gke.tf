resource "google_container_cluster" "primary" {
  name           = "primary-cluster"
  location       = "${var.region}"
  node_locations = ["${var.zone_1}"]
  provider       = "google-beta"

  # We can't create a cluster with no node pool defined, but we want to only use
  # separately managed node pools. So we create the smallest possible default
  # node pool and immediately delete it.
  remove_default_node_pool = true
  initial_node_count       = 1

  network = "${google_compute_network.private_network.self_link}"

  master_auth {
    username = ""
    password = ""

    client_certificate_config {
      issue_client_certificate = false
    }
  }

  # Enable Stackdriver Kubernetes monitoring and logging
  logging_service    = "logging.googleapis.com/kubernetes"
  monitoring_service = "monitoring.googleapis.com/kubernetes"
  # Allows us to see flow logs for all traffic
  # between Pods, including traffic between Pods on the same node. See
  # https://cloud.google.com/kubernetes-engine/docs/how-to/intranode-visibility
  enable_intranode_visibility = true


  ip_allocation_policy {
    # Enable use of alias IPs for pod IPs. This makes the cluster
    # VPC-native which is a requirement in order to network privately with Cloud
    # SQL. See https://cloud.google.com/kubernetes-engine/docs/how-to/alias-ips
    use_ip_aliases = true
  }

  vertical_pod_autoscaling {
    enabled = true
  }

  addons_config {
    # Overrides default addon list and disables the built-in GCE Ingress controller (HttpLoadBalancing addon), since we are
    # using nginx-ingress. See https://stackoverflow.com/a/54407496/1446678
    http_load_balancing {
      disabled = true
    }
  }

  maintenance_policy {
    daily_maintenance_window {
      start_time = "02:00"
    }
  }
}

resource "google_container_node_pool" "primary_preemptible_nodes" {
  name     = "${var.pool_preemptible_name}"
  cluster  = "${google_container_cluster.primary.name}"
  location = "${var.region}"

  depends_on = [
    "google_container_cluster.primary"
  ]
  node_count = "${var.pool_preemptible_node_count}"
  provider   = "google-beta"

  management {
    auto_repair  = true
    auto_upgrade = true
  }

  node_config {
    preemptible     = true
    machine_type    = "${var.pool_preemptible_machine_type}"
    service_account = "${google_service_account.cluster-node.email}"
    disk_size_gb    = 50


    metadata = {
      disable-legacy-endpoints = "true"
    }

    # Not stable yet
    # image_type      = "COS_CONTAINERD"
    # sandbox_config {
    #   sandbox_type = "gvisor"
    # }

    oauth_scopes = [
      "https://www.googleapis.com/auth/cloud-platform",
      "https://www.googleapis.com/auth/logging.write",
      "https://www.googleapis.com/auth/monitoring",
      "https://www.googleapis.com/auth/sqlservice.admin"
    ]
  }
}


output "cluster-name" {
  value = "${google_container_cluster.primary.name}"
}
