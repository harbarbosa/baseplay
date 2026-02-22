class TeamContextState {
  final int? activeTeamId;
  final int? activeCategoryId;
  final bool isLoaded;

  const TeamContextState({
    this.activeTeamId,
    this.activeCategoryId,
    this.isLoaded = false,
  });

  TeamContextState copyWith({
    Object? activeTeamId = _unset,
    Object? activeCategoryId = _unset,
    bool? isLoaded,
  }) {
    return TeamContextState(
      activeTeamId: activeTeamId == _unset
          ? this.activeTeamId
          : activeTeamId as int?,
      activeCategoryId: activeCategoryId == _unset
          ? this.activeCategoryId
          : activeCategoryId as int?,
      isLoaded: isLoaded ?? this.isLoaded,
    );
  }
}

const Object _unset = Object();
