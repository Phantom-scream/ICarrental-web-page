# Car Rental Web Application

This is a web-based **Car Rental System** that allows users to register, view car details, and book rentals. The application is built using **PHP** and is containerized with **Docker**, deployed using **Kubernetes** for scalability and orchestration.

## 🚗 Features

* User registration and authentication
* View available cars with details
* Book cars for specific dates
* Admin functionalities to manage bookings and cars
* Responsive UI

## 🛠️ Technologies Used

* **Frontend:** HTML, CSS
* **Backend:** PHP
* **Database:** MySQL (you may need to configure separately if not included in the container)
* **Containerization:** Docker
* **Orchestration:** Kubernetes

## 📁 Project Structure

```
Car-rent/
├── ICarrental/
│   ├── add.php
│   ├── auth.php
│   ├── booking.php
│   ├── cardetails.php
│   ├── login.php
│   ├── register.css
│   ├── userstorage.php
│   ├── ...
│   ├── Dockerfile
│   └── deployment.yaml
```

## 🐳 Docker

### Build Docker Image

To build the Docker image for the PHP application:

```bash
docker build -t car-rental-app .
```

### Run Docker Container

```bash
docker run -p 8080:80 car-rental-app
```

Visit `http://localhost:8080` in your browser.

## ☘️ Kubernetes

### Prerequisites

* Minikube or a Kubernetes cluster
* kubectl installed and configured

### Deploy to Kubernetes

1. **Apply Deployment**

```bash
kubectl apply -f deployment.yaml
```

2. **Expose the Service**

If your `deployment.yaml` doesn't already include a `Service`, you can expose it manually:

```bash
kubectl expose deployment car-rental --type=LoadBalancer --port=80 --target-port=80
```

3. **Access the Application**

Use `minikube service car-rental` or get the external IP via:

```bash
kubectl get svc
```

## 📝 Notes

* Make sure the MySQL database (if required) is running and accessible. You can deploy it separately in Kubernetes using a Helm chart or YAML config.
* Update `deployment.yaml` if you need to configure environment variables or volume mounts for database credentials, storage, etc.

## 📄 License

This project is open-source and available under the MIT License.
