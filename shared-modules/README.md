# Módulos Compartilhados - FastrackGPS

Esta pasta contém recursos compartilhados entre os sistemas legacy e moderno.

## 📁 Estrutura

### `/config/` - Configurações Comuns
- Configurações de banco de dados
- Variáveis de ambiente compartilhadas
- Configurações de API (Google Maps, etc.)

### `/assets/` - Assets Compartilhados
- CSS comum entre sistemas
- JavaScript compartilhado
- Imagens e ícones
- Fonts e outros recursos estáticos

### `/database/` - Banco de Dados
- Scripts de migração
- Esquemas compartilhados
- Dados de seed

### `/scripts/` - Scripts de Sistema
- Scripts de instalação
- Scripts de manutenção
- Utilitários de backup
- Deploy e CI/CD

### `/docs/` - Documentação
- Documentação técnica
- Guias de instalação
- READMEs do projeto

## 🔗 Como Usar

### No Sistema Legacy
```php
// Incluir configuração compartilhada
require_once '../shared-modules/config/database.php';

// Usar assets compartilhados
<link rel="stylesheet" href="../shared-modules/assets/css/common.css">
```

### No Sistema Moderno
```php
// Autoloader já configurado para shared-modules
use SharedModules\Config\DatabaseConfig;

// Assets via public symlinks
<link rel="stylesheet" href="/shared/css/common.css">
```

## ⚙️ Configuração

### Symlinks para Assets
```bash
# No diretório public/ do sistema moderno
ln -s ../../shared-modules/assets shared

# No diretório do sistema legacy  
ln -s ../shared-modules/assets shared
```

### Variáveis de Ambiente
```bash
# shared-modules/config/.env.shared
DB_HOST=localhost
DB_NAME=tracker2
GOOGLE_MAPS_API_KEY=your_key_here
```

## 🚨 Importante

- **NÃO modifique** sem testar ambos os sistemas
- **Mantenha compatibilidade** entre legacy e moderno
- **Use versionamento** para mudanças críticas
- **Documente** todas as alterações

## 📝 Convenções

### CSS Compartilhado
- Prefixo `.shared-` para classes globais
- Evitar especificidade alta
- Usar variáveis CSS para temas

### JavaScript Compartilhado
- Namespace `SharedModules`
- Compatibilidade ES5 para legacy
- Evitar dependências pesadas

### Configurações
- Arquivo `.env.shared` para variáveis comuns
- Configs específicos em cada sistema
- Validação em ambos os sistemas