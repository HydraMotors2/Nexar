# NEXAR

**Plataforma SaaS Moderna - Conectando Clientes e Fornecedores**

![NEXAR Banner](./public/assets/images/banner.png)

## Visão Geral

NEXAR é uma plataforma SaaS de ponta projetada para conectar perfeitamente clientes com fornecedores. Construída com tecnologias modernas e apresentando uma interface premium com modo escuro, acentos neon laranja, efeitos glassmorphism e animações suaves.

## Recursos

- 🎨 **Interface Premium Modo Escuro** - Fundo preto fosco com acentos neon laranja
- ✨ **Design Glassmorphism** - Efeitos modernos de vidro fosco
- 📱 **Totalmente Responsiva** - Funciona em todos os dispositivos
- 🚀 **Animações Suaves** - Alimentadas por CSS moderno e JavaScript
- 🔒 **Autenticação Segura** - Sistema de login seguro baseado em PHP
- ⚡ **Suporte TypeScript** - JavaScript type-safe
- 🧩 **Arquitetura Modular** - Codebase escalável e mantível

## Pilha de Tecnologia

- **Frontend**: HTML5, CSS3, JavaScript, TypeScript
- **Backend**: PHP 8.0+
- **Banco de Dados**: MySQL/MariaDB
- **Ferramentas de Build**: Node.js, npm

## Estrutura do Projeto

```
NEXAR/
├── public/                 # Raiz web pública
│   ├── assets/            # Ativos estáticos
│   │   ├── images/        # Imagens e ícones
│   │   └── fonts/         # Fontes customizadas
│   ├── css/               # CSS compilado
│   ├── js/                # JavaScript compilado
│   ├── ts/                # Arquivos fonte TypeScript
│   └── index.php          # Ponto de entrada principal
├── src/                   # Arquivos fonte
│   ├── css/               # Arquivos fonte CSS
│   │   ├── variables.css  # Propriedades CSS customizadas
│   │   ├── global.css     # Estilos globais
│   │   └── components/    # Estilos de componentes
│   └── ts/                # Fonte TypeScript
├── components/            # Componentes PHP reutilizáveis
├── pages/                 # Templates de página
├── php/                   # Utilitários PHP
├── api/                   # Endpoints de API
├── database/              # Esquemas e migrações de banco de dados
└── config/                # Arquivos de configuração
```

## Começando

### Pré-requisitos

- PHP 8.0 ou superior
- MySQL 5.7+ ou MariaDB 10.3+
- Node.js 18+ e npm
- Composer (opcional, para dependências PHP)

### Instalação

1. Clone o repositório:
```bash
git clone https://github.com/nexar/nexar.git
cd nexar
```

2. Instale as dependências PHP (opcional):
```bash
composer install
```

3. Instale as dependências Node.js:
```bash
npm install
```

4. Configure seu ambiente:
```bash
cp .env.example .env
# Edite .env com suas credenciais de banco de dados
```

5. Configure o banco de dados:
```bash
php database/migrate.php
```

6. Compile os ativos:
```bash
npm run build
```

7. Inicie o servidor de desenvolvimento:
```bash
npm run dev
```

## Configuração

Copie `.env.example` para `.env` e configure:

```env
APP_NAME=NEXAR
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nexar
DB_USERNAME=root
DB_PASSWORD=

MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
```

## Documentação da API

Endpoints de API são documentados no diretório `/api`. Todos os endpoints retornam respostas JSON.

### Endpoints de Autenticação

- `POST /api/auth/register` - Registrar novo usuário
- `POST /api/auth/login` - Login do usuário
- `POST /api/auth/logout` - Logout do usuário
- `GET /api/auth/me` - Obter usuário atual

### Endpoints de Usuário

- `GET /api/users` - Listar usuários
- `GET /api/users/{id}` - Obter usuário por ID
- `PUT /api/users/{id}` - Atualizar usuário
- `DELETE /api/users/{id}` - Deletar usuário

## Contribuindo

1. Faça um fork do repositório
2. Crie sua branch de recurso (`git checkout -b feature/recurso-incrivel`)
3. Faça commit de suas alterações (`git commit -m 'Adicionar recurso incrível'`)
4. Push para a branch (`git push origin feature/recurso-incrivel`)
5. Abra um Pull Request

## Licença

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support, email support@nexar.com or join our Slack channel.

---

**NEXAR** - Connecting Clients and Providers Seamlessly