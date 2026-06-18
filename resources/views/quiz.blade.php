@extends('layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="card quiz-hero p-4 p-lg-5">
            @if(session('result'))
            <div class="alert alert-{{ session('correct') ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
                {{ session('result') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="row align-items-center g-4 mb-4">
                <div class="col-lg-8">
                    <span class="hero-chip mb-3">
                        <i class="fas fa-bolt"></i>
                        Desafio ativo
                    </span>
                    <h1 class="mb-3">
                        <i class="fas fa-question-circle text-primary me-2"></i>
                        {{ $categoryDisplayLabel ?? $category->name }}
                    </h1>
                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <span class="badge bg-secondary fs-6">Questao {{ $current_question }} de {{ $total_questions }}</span>
                        <span class="badge bg-warning text-dark fs-6">XP total: {{ $totalXp }}</span>
                        <span class="badge bg-success fs-6">+{{ $xpPerCorrect }} XP por acerto</span>
                        @if(!empty($questionDifficultyLabel))
                            <x-difficulty-seal :label="$questionDifficultyLabel" />
                        @endif
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        @if(!empty($categorySubjectLabel))
                            <span class="badge bg-primary fs-6">{{ $categorySubjectLabel }}</span>
                        @endif
                        <span class="badge bg-info text-dark">{{ $quizType === 'multiple_choice' ? 'Multipla escolha' : 'Vocabulario' }}</span>
                    </div>
                </div>

                <div class="col-lg-4 text-center">
                    <img src="{{ asset('assets/marthina-theme/images/customization.svg') }}" alt="Ilustracao do desafio" class="img-fluid" style="max-width: 230px;">
                </div>
            </div>

            <div class="text-center mb-3">
                <h2 class="display-5 fw-bold text-primary">{{ $questionPrompt }}</h2>

                @if($questionSupportText)
                    <p class="text-muted mt-3 mb-0">{{ $questionSupportText }}</p>
                @endif

                @if($ttsText)
                    <button onclick="speakWord()" class="btn btn-custom mt-3 mb-3">
                        <i class="fas fa-volume-up me-2"></i>Ouvir
                    </button>
                @endif
            </div>

            <form id="quizForm" action="/quiz/{{ $category_id }}/check" method="POST">
                @csrf
                <input type="hidden" name="{{ $recordField }}" value="{{ $recordId }}">
                <h4 class="text-center mb-4">{{ $questionHelperText }}</h4>

                <div class="row">
                    @foreach($answers as $index => $answer)
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="answer" id="option{{ $index }}" value="{{ $answer['value'] }}">
                            <label class="form-check-label d-flex align-items-center p-3 p-lg-4 border rounded-4 w-100" for="option{{ $index }}">
                                <span class="fw-bold me-2">{{ $answer['key'] }}.</span> {{ $answer['label'] }}
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-custom btn-lg px-5">
                        <i class="fas fa-check me-2"></i>Responder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function speakWord() {
    const text = @json($ttsText);
    if (!text) {
        return;
    }

    let speech = new SpeechSynthesisUtterance(text);
    speech.lang = "en-US";
    window.speechSynthesis.speak(speech);
}

document.getElementById('quizForm').addEventListener('submit', function(e) {
    const selected = document.querySelector('input[name="answer"]:checked');
    if (!selected) {
        e.preventDefault();
        alert('Escolha uma opcao antes de enviar.');
    }
});
</script>
@endsection
