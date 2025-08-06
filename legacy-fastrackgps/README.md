# FastrackGPS - Sistema Legacy

Este diretório contém o sistema **FastrackGPS original** com código PHP legacy.

## ⚠️ Sistema Legacy

Este é o sistema antigo que ainda pode estar em produção. Inclui:

- **PHP tradicional** (versões mais antigas)
- **Estrutura de frames** (HTML framesets)
- **Consultas SQL diretas** (mysql_* functions)
- **Arquitetura monolítica**

## Estrutura do Sistema

- `/administracao/` - Painel administrativo
- `/ajax/` - Endpoints AJAX
- `/OBD/` - Interface OBD
- `/server/` - Lógica do servidor de rastreamento
- `/webservices/` - APIs externas
- `/imagens/` - Assets estáticos
- `/javascript/` - Scripts client-side

## ⚠️ Avisos Importantes

1. **Não modifique** este código sem backup completo
2. **Sistema em produção** - mudanças podem quebrar funcionalidades
3. **Migração recomendada** para o sistema moderno
4. **Vulnerabilidades de segurança** conhecidas

## Configuração

As configurações estão espalhadas em vários arquivos:
- `config.php`
- `mysql.php`
- `Connection.simple.php`
- Arquivos `config.php` em subdiretórios

## Para Nova Funcionalidade

**Use o sistema moderno** em `/modern-fastrackgps/` para novas funcionalidades.

## Migração

Consulte o `/modern-fastrackgps/` para o sistema refatorado com:
- PHP 8.1+
- Arquitetura moderna
- Padrões de segurança
- Testes automatizados