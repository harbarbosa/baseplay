import 'package:flutter/material.dart';

import '../auth/permissions.dart';

class NavItem {
  final String route;
  final String label;
  final IconData iconOutlined;
  final IconData iconFilled;
  final List<String> requiredPermissions;

  const NavItem({
    required this.route,
    required this.label,
    required this.iconOutlined,
    required this.iconFilled,
    this.requiredPermissions = const [],
  });
}

const navItems = <NavItem>[
  NavItem(
    route: '/home/agenda',
    label: 'Agenda',
    iconOutlined: Icons.calendar_month_outlined,
    iconFilled: Icons.calendar_month,
    requiredPermissions: [Permissions.eventsView],
  ),
  NavItem(
    route: '/home/notices',
    label: 'Avisos',
    iconOutlined: Icons.campaign_outlined,
    iconFilled: Icons.campaign,
    requiredPermissions: [Permissions.noticesView],
  ),
  NavItem(
    route: '/home/athletes',
    label: 'Atletas',
    iconOutlined: Icons.groups_2_outlined,
    iconFilled: Icons.groups_2,
    requiredPermissions: [Permissions.athletesView],
  ),
  NavItem(
    route: '/home/documents',
    label: 'Documentos',
    iconOutlined: Icons.folder_copy_outlined,
    iconFilled: Icons.folder_copy,
    requiredPermissions: [Permissions.documentsViewSelf],
  ),
  NavItem(
    route: '/home/profile',
    label: 'Painel',
    iconOutlined: Icons.dashboard_outlined,
    iconFilled: Icons.dashboard,
  ),
];

List<NavItem> visibleNavItems(Set<String> permissions) {
  final items = navItems
      .where((item) {
        if (item.requiredPermissions.isEmpty) {
          return true;
        }
        return item.requiredPermissions.any(permissions.contains);
      })
      .toList(growable: false);

  if (items.isEmpty) {
    return navItems
        .where((item) => item.route == '/home/profile')
        .toList(growable: false);
  }

  return items;
}

List<String> allowedHomeRoutes(Set<String> permissions) {
  return visibleNavItems(permissions).map((item) => item.route).toList();
}
