@if ($students->isEmpty())
    @include('attendance.partials.monthly-grid-empty')
@else
    <div class="attm-grid-wrap">
        <table class="attm-grid">
            <thead>
                <tr>
                    <th>Student</th>
                    @foreach ($days as $day)
                        <th
                            class="{{ $day['isWeekend'] ? 'attm-day-weekend' : '' }} {{ $day['isToday'] ? 'attm-day-today' : '' }}">
                            @if ($day['isWeekend'])
                                <div>{{ $day['day'] }}</div>
                                <div class="attm-weekend-label">Sunday</div>
                            @else
                                {{ $day['day'] }}
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach ($students as $student)
                    @php
                        $avatar = $student->profile_image
                            ? asset('uploads/students/' . $student->profile_image)
                            : 'https://ui-avatars.com/api/?name=' .
                                urlencode($student->student_name) .
                                '&background=2563eb&color=fff&size=64';
                        $roll = $student->roll_no ?: 'N/A';
                    @endphp

                    <tr>
                        <td class="attm-student-cell">
                            <div class="attm-student">
                                <img class="attm-avatar" src="{{ $avatar }}" alt="Student">
                                <div>
                                    <div class="attm-name">{{ $student->student_name }}</div>
                                    <div class="attm-sub">Roll: {{ $roll }}</div>
                                </div>
                            </div>
                        </td>

                        @foreach ($days as $day)
                            @php
                                $status = $attendanceMap[$student->id][$day['date']] ?? null;
                                $editable = $canEdit && !$day['isWeekend'] && $day['date'] === $editableDate;
                            @endphp

                            <td class="attm-cell  {{ $editable ? 'attm-cell-editable' : 'attm-cell-lock' }} 
                                   {{ $day['isWeekend'] ? 'attm-day-weekend' : '' }} 
                                   {{ $day['isToday'] ? 'attm-day-today' : '' }}"
                                data-student-id="{{ $student->id }}" data-date="{{ $day['date'] }}"
                                data-label="{{ $day['label'] }}" data-status="{{ $status ?? '' }}"
                                data-cell-id="attm-cell-{{ $student->id }}-{{ $day['day'] }}"
                                data-tooltip="{{ ($status ? ucfirst($status) : 'No status') . ' on ' . $day['label'] }}">

                                @if (!$day['isWeekend'])
                                    @switch($status)
                                        @case('present')
                                            <span class="attm-icon present">&#10003;</span>
                                        @break

                                        @case('absent')
                                            <span class="attm-icon absent">&#10005;</span>
                                        @break

                                        @default
                                            <span class="attm-icon">-</span>
                                    @endswitch
                                @endif

                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
