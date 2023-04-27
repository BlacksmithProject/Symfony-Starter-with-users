# BlacksmithProject: Symfony Starter with users

## Why:

This logic is re-implemented each time I start a new side-project.

### Included:

Provides multiple use-cases:

- email/password registration
- activation
- authentication
- forgotten password declaration
- password reset
- console command to register a user (include both registration and activation)
- console command to authenticate a user
- console command to reset user's password (include both declaration and reset)

### Not included:

- email for activation (it only provides tokens)
- email for forgotten password declaration (it only provides tokens)

### Project structure:

This is a sort of _pragmatic_ Hexagonal architecture:

```
.
├── Security                    # Security Users and Tokens
│       ├── Domain              # Business Logic
│       │   ├── ...
│       │   ├── Exception       
│       │   ├── Model           
│       │   ├── Ports           # Interfaces to interact with outside (like storage)
│       │   │
│       │   ├── UseCase         # Specific useCase, like "Registration". THIS SHOULD BE YOUR DOMAIN ENTRYPOINT
│       │   │
│       │   └── ValueObject
│       ├── Infrastructure      # Technical Logic
│       │   ├── Adapters        # Adapters for the Domain ports (you can modify those adapters)
│       │   ├── Console         # Functionnal console commands
│       │   ├
│       │   └── ...             # YOU CAN IMPLEMENT HTTP ENTRYPOINTS HERE
│       └── ...
└── ...
```

## Prerequisites:

- [🐳 docker compose v2.10+](https://docs.docker.com/compose/install/)

## HowTo:

- init the project with `make init` and visit http://localhost:8092
- see [Makefile](./Makefile) (try `make help`)

## Troubleshooting:

You might have some ports already used (event though i tried to use unused ports), modify
[docker-compose.override.yml](./docker-compose.override.yml) at your convenience.

## Urls:

- Your project on http://localhost:8092
- Phpmyadmin on http://localhost:8093