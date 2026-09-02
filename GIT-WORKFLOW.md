# Flujo Git — Entre Sabores

Reglas de trabajo en equipo. **`main`** es la rama estable; **`develop`** integra features antes de promover a producción.

## Ramas

| Rama | Rol |
|------|-----|
| `main` | Código en producción o listo para desplegar |
| `develop` | Integración de features ya revisadas |
| `feature/*` | Una rama por tarea; **siempre creada desde `main`** |

## Ciclo por tarea

```mermaid
gitGraph
    commit id: "main estable"
    branch feature/mi-tarea
    checkout feature/mi-tarea
    commit id: "desarrollo"
    checkout develop
    merge feature/mi-tarea id: "PR → develop"
    checkout main
    merge develop id: "PR → main (Vercel)"
```

```text
main ──(checkout -b)──► feature/mi-tarea ──(PR)──► develop ──(PR)──► main
         ▲                                              │
         └──────── siguiente feature parte de main ─────┘
```

### 1. Empezar tarea

```bash
git fetch origin
git checkout main
git pull origin main
git checkout -b feature/nombre-corto
```

### 2. Trabajar y subir

```bash
# commits en la feature…
git push -u origin feature/nombre-corto
```

### 3. Integrar en develop

Abrir PR en GitHub:

- **Base:** `develop`
- **Compare:** `feature/nombre-corto`

Revisión, CI verde, merge a `develop`.

### 4. Promover a main

Cuando `develop` esté listo para liberar:

- **Base:** `main`
- **Compare:** `develop`

Merge a `main` (dispara despliegue en Vercel — **solo esta rama**; ver [VERCEL.md](VERCEL.md)).

## Reglas

1. **Cada feature parte de `main`**, no de `develop` (evita arrastrar trabajo ajeno o conflictos ocultos).
2. **Cada PR de feature apunta a `develop`**, no a `main`.
3. **Solo `develop` → `main`** promueve integración a la rama estable.
4. **No commits directos** en `main` ni `develop` (salvo emergencia acordada en equipo).
5. **Una tarea, una rama**; no acumular cambios no relacionados.
6. **Vercel despliega únicamente `main`**; `develop` y features no generan build en Vercel.

## Nombres sugeridos

- `feature/project-index`
- `feature/wall-feed-cache`
- `feature/fix-notificaciones-unread`

## Hotfix (excepción)

Corrección urgente en producción: rama `hotfix/*` **desde `main`**, PR a `main` y **backport/merge a `develop`** para no diverger.

## Plantilla de tarjeta de tarea

Al planificar una tarea en el tablero del equipo, puedes usar este formato:

| Sección | Contenido |
|---------|-----------|
| Título (`#`) | Nombre de la rama, ej. `feature/project-index` |
| Nombre de la tarea | Título legible |
| Descripción | Alcance y contexto |
| Objetivo | Problema que resuelve o valor que aporta |

Documento versionado enlazado desde [README.md](README.md#gitflow-del-proyecto).
