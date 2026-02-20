import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../state/providers.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _email = TextEditingController();
  final _password = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  @override
  void dispose() {
    _email.dispose();
    _password.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(authControllerProvider);
    ref.listen(authControllerProvider, (prev, next) {
      if (prev?.isLoading == true &&
          next.isLoading == false &&
          next.error == null) {
        if (mounted) {
          context.go('/home/agenda');
        }
      }
    });

    return Scaffold(
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Card(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Form(
                key: _formKey,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Image.asset('assets/images/baseplay-logo.png', height: 72),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _email,
                      decoration: const InputDecoration(labelText: 'E-mail'),
                      keyboardType: TextInputType.emailAddress,
                      validator: (value) => (value == null || value.isEmpty)
                          ? 'Informe o e-mail'
                          : null,
                    ),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: _password,
                      decoration: const InputDecoration(labelText: 'Senha'),
                      obscureText: true,
                      validator: (value) => (value == null || value.isEmpty)
                          ? 'Informe a senha'
                          : null,
                    ),
                    const SizedBox(height: 16),
                    if (state.error != null)
                      Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: Text(
                          state.error!.replaceAll('Exception: ', ''),
                          style: const TextStyle(color: Colors.red),
                        ),
                      ),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: state.isLoading
                            ? null
                            : () {
                                if (_formKey.currentState?.validate() != true) {
                                  return;
                                }
                                ref
                                    .read(authControllerProvider.notifier)
                                    .login(_email.text.trim(), _password.text);
                              },
                        child: state.isLoading
                            ? const SizedBox(
                                height: 18,
                                width: 18,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                ),
                              )
                            : const Text('Entrar'),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
