import 'package:flutter/material.dart';
import '../widgets/team_selector_action.dart';

class ProfilePlaceholderScreen extends StatelessWidget {
  const ProfilePlaceholderScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Perfil'),
        actions: const [TeamSelectorAction()],
      ),
      body: const Center(child: Text('Perfil (em breve)')),
    );
  }
}
