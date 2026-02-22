import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../state/providers.dart';
import '../../core/navigation/nav_items.dart';

class MainShellScreen extends ConsumerWidget {
  final Widget child;
  final String location;

  const MainShellScreen({
    super.key,
    required this.child,
    required this.location,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final permissions = ref.watch(authUserProvider)?.capabilities.toSet() ??
        ref.watch(authUserProvider)?.permissions.toSet() ??
        <String>{};
    final destinations = visibleNavItems(permissions);
    final currentIndex = _indexFromLocation(location, destinations);

    return Scaffold(
      body: child,
      bottomNavigationBar: NavigationBar(
        selectedIndex: currentIndex,
        onDestinationSelected: (index) {
          context.go(destinations[index].route);
        },
        destinations: destinations
            .map(
              (item) => NavigationDestination(
                icon: Icon(item.iconOutlined),
                selectedIcon: Icon(item.iconFilled),
                label: item.label,
              ),
            )
            .toList(growable: false),
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,
      floatingActionButton: currentIndex == 3 ||
              (currentIndex >= 0 &&
                  destinations[currentIndex].route == '/home/profile')
          ? null
          : FloatingActionButton.small(
              tooltip: 'Sair',
              onPressed: () async {
                await ref.read(authControllerProvider.notifier).logout();
                if (context.mounted) {
                  context.go('/login');
                }
              },
              child: const Icon(Icons.logout),
            ),
    );
  }

  int _indexFromLocation(String value, List<NavItem> items) {
    final idx = items.indexWhere((item) => value.startsWith(item.route));
    if (idx >= 0) {
      return idx;
    }
    return 0;
  }
}
