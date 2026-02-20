# Documentos Overview

## Objetivo
Centralizar pendencias e conformidade de documentos em uma tela unica.

## Rota
- `/documents/overview`

## Cards
- Vencidos
- A vencer (prazo configuravel: 7/30/90)
- Faltando obrigatorio
- Aguardando aprovacao (quando status existir)

## Conformidade por categoria
- Baseada em documentos exigidos por categoria.
- Tabela `category_required_documents` define obrigatoriedade.

## Pendencias criticas
- Lista top 15 combinando vencidos, a vencer e faltas obrigatorias.

## Filtros
- Equipe, categoria, tipo de documento, status, prazo.
