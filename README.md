# dockerexample

Aplicación PHP + MySQL desplegada con Docker. Lista de tareas con operaciones CRUD.

## Estructura

```
dockerexample/
├── docker-compose.yml
├── Dockerfile
├── index.php
└── docker/
    └── mysql/
        └── init.sql
```

## Despliegue automático (servidor escolar)

Al hacer `git push` a `main`, el webhook del servidor:

1. Clona o actualiza el repo en `/home/alumnoXX/proyectos/dockerexample/`
2. Ejecuta `docker compose up -d --build`

La aplicación queda accesible en:

```
http://<IP-servidor>/node/alumnoXX/dockerexample/
```

## Probar localmente

```bash
# Con el puerto asignado (alumno usa 4023)
PORT=4023 docker compose up --build

# Puerto por defecto (8080) si no se especifica PORT
docker compose up --build
```

Abre http://localhost:4023 (o el puerto que hayas indicado).

## Variables de entorno

| Variable | Descripción | Valor por defecto |
|---|---|---|
| `PORT` | Puerto del host mapeado al contenedor app | `8080` |

El servidor inyecta `PORT=4023` automáticamente al levantar el proyecto.

## Recursos por contenedor

| Servicio | RAM | CPU |
|---|---|---|
| app | 512 MB | 0.5 |
| db | 512 MB | 0.5 |
| **Total** | **1 GB** | **1 CPU** |
