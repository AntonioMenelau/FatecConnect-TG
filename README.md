<<<<<<< HEAD
# FatecConnect-TG
FatecConnect é uma aplicação web para conectar usuários, compartilhar posts e interagir em uma timeline. O projeto inclui autenticação, gerenciamento de perfil e recursos de postagem/comentários.
=======
# FatecConnect

FatecConnect é uma aplicação web para conectar usuários, compartilhar posts e interagir em uma timeline. O projeto inclui autenticação, gerenciamento de perfil e recursos de postagem/comentários.

## Estrutura do Projeto

```
fatecconnect/
├── assets/
│   ├── css/
│   │   └── styles.css         # Estilos CSS do projeto
│   ├── js/
│   │   └── scripts.js         # Scripts JavaScript
│   └── images/
│       └── logo.png           # Logo e imagens de perfil
├── includes/
│   ├── config.php             # Configurações globais
│   ├── db.php                 # Conexão e queries do banco de dados (SQLite)
│   ├── delete-post.php        # Funções Administrativa
│   ├── functions.php          # Funções utilitárias (auth, posts, etc)
│   ├── header.php             # Cabeçalho HTML
│   └── footer.php             # Rodapé HTML
├── pages/
│   ├── create-post.php        # Página de Criação de post
│   ├── login.php              # Página de login
│   ├── manage-posts.php       # Página Administrativa
│   ├── post.php               # Detalhes de um post
│   ├── profile.php            # Perfil do usuário
│   ├── register.php           # Página de registro
│   └── timeline.php           # Timeline de posts
├── banc.sql                   # Arquivo SQL
├── fatecconnect.sqlite        # Arquivo SQLite para o Banco de Dados
├── index.php                  # Página inicial
├── logout.php                 # Logout do usuário
└── README.md                  # Documentação do projeto
```

## Instalação

1. **Clone o repositório:**
   ```sh
   git clone https://github.com/yourusername/FatecConnect.git
   cd FatecConnect
   ```

2. **Configuração do Banco de Dados:**
   - O projeto utiliza SQLite por padrão.
   - O arquivo do banco será criado automaticamente como `fatecconnect.sqlite` na raiz do projeto.
   - Se necessário, ajuste configurações em `includes/config.php`.

3. **Configuração do Servidor:**
   - Configure seu servidor web (ex: XAMPP, WAMP, Apache) para servir a pasta do projeto.
   - Certifique-se de que o PHP tenha permissão de escrita na pasta do projeto para criar o banco SQLite e salvar imagens de perfil.

4. **Primeira Execução:**
   - Acesse o sistema pelo navegador (ex: http://localhost/fatecconnect).
   - Um usuário administrador padrão será criado automaticamente:
     - **E-mail:** admin@fatec.sp.gov.br
     - **Senha:** admin123

## Uso

- Faça login ou registre-se para acessar a timeline.
- Crie posts, comente e edite seu perfil.
- Gerencie sua bio e foto de perfil na página de perfil.

## Tecnologias Utilizadas

- **PHP** (>=7.4)
- **SQLite** (banco de dados local)
- **Tailwind CSS** (via CDN)
- **Font Awesome** (ícones)
- **HTML5/CSS3/JS**
>>>>>>> 13523fc (Primeiro commit do projeto FatecConnect-TG)
