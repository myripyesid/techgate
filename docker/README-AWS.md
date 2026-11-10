# Despliegue de TechGate en AWS (entregable #2)

La aplicación se empaqueta con el `Dockerfile` de la raíz. La misma imagen
sirve para cualquiera de estas tres opciones de AWS. **App Runner** es la más
simple y la recomendada para la entrega.

> Requisitos previos: tener el **AWS CLI** configurado (`aws configure`) y
> **Docker** instalado localmente.

---

## Opción A — AWS App Runner (recomendada, la más simple)

1. **Crear un repositorio en ECR** (registro de imágenes de AWS):
   ```bash
   aws ecr create-repository --repository-name techgate --region us-east-1
   ```

2. **Autenticar Docker contra ECR** (reemplaza `<ACCOUNT_ID>`):
   ```bash
   aws ecr get-login-password --region us-east-1 \
     | docker login --username AWS --password-stdin <ACCOUNT_ID>.dkr.ecr.us-east-1.amazonaws.com
   ```

3. **Construir, etiquetar y subir la imagen**:
   ```bash
   docker build -t techgate .
   docker tag techgate:latest <ACCOUNT_ID>.dkr.ecr.us-east-1.amazonaws.com/techgate:latest
   docker push <ACCOUNT_ID>.dkr.ecr.us-east-1.amazonaws.com/techgate:latest
   ```

4. **Crear el servicio App Runner** (consola web de AWS):
   - Source: *Container registry* → *Amazon ECR* → la imagen `techgate:latest`.
   - Port: **80**.
   - Variables de entorno (opcional): `ALLIED_PRODUCTS_URL` con la URL del
     servicio del equipo anterior (grupo 3 - Tienda de Joyas).
   - App Runner entrega una URL pública `https://xxxx.awsapprunner.com`.

---

## Opción B — Elastic Beanstalk (plataforma Docker)

1. Instalar la EB CLI: `pip install awsebcli`.
2. Desde la raíz del proyecto:
   ```bash
   eb init -p docker techgate --region us-east-1
   eb create techgate-env
   ```
   Elastic Beanstalk usa el `Dockerfile` (o `Dockerrun.aws.json` si se publica
   desde ECR) y expone una URL pública.

---

## Opción C — Amazon ECS (Fargate)

1. Subir la imagen a ECR (pasos 1–3 de la Opción A).
2. Crear un *Task Definition* que use esa imagen, puerto 80, y un *Service*
   en un cluster Fargate con un Application Load Balancer.

---

## Base de datos

La app usa **SQLite** por defecto (archivo dentro del contenedor), suficiente
para la demostración. El `entrypoint.sh` crea la base y la siembra al arrancar.

Para datos persistentes en producción se recomienda **Amazon RDS**
(MySQL/PostgreSQL) ajustando las variables `DB_*` del contenedor.

---

## Después del despliegue

1. Copiar la **URL pública** que entrega AWS.
2. Entregar al docente: el **link del repositorio** y esa **URL**.
3. Pasar al equipo siguiente (grupo 5 - NFT) la URL del servicio:
   `https://<tu-url-aws>/api/productos`
4. Configurar `ALLIED_PRODUCTS_URL` con la URL que les dé el equipo anterior
   (grupo 3 - Joyas): su endpoint de productos en JSON.
